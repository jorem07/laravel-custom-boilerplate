<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait SearchGenerator
{
    public function scopeSearchColumns($query, array $searches)
    {
        $excluded = ['email', 'password'];

        $filteredSearches = array_filter($searches, function ($search) use ($excluded) {
            return !in_array($search['key'] ?? '', $excluded, true);
        });

        return $query->when(!empty($filteredSearches), function ($q) use ($filteredSearches) {
            $q->where(function ($sub) use ($filteredSearches) {

                $search_value = collect($filteredSearches)
                    ->filter(fn($q) => isset($q['key'], $q['value']))
                    ->groupBy('key')
                    ->map(fn($items) => $items->pluck('value')->values()->toArray())
                    ->toArray();

                $search_between = collect($filteredSearches)
                    ->filter(fn($q) => isset($q['key'], $q['between']))
                    ->groupBy('key')
                    ->map(fn($items) => $items->pluck('between')->values()->toArray())
                    ->toArray();

                if (!empty($search_value)) {
                    foreach ($search_value as $key => $values) {
                        if (Str::contains($key, '.')) {
                            $this->applyNestedRelationWhereIn($sub, $key, $values);
                        } else {
                            $this->applyWhereIn($sub, $key, $values);
                        }
                    }
                }

                if (!empty($search_between)) {
                    foreach ($search_between as $key => $value) {
                        $sub->whereBetween($key, $value);
                    }
                }
            });
        });
    }

    private function applyWhereIn($query, $column, $value)
    {
        $table = $query->getModel()->getTable();
        $type  = Schema::getColumnType($table, $column);

        if (in_array($type, ['int4', 'int8', 'bool', 'date', 'timestamp'])) {
            $query->orWhereIn($column, $value);
        } elseif ($type === 'json') {
            if (is_string($value)) {
                // split by comma or space (one or more spaces)
                $value = preg_split('/[\s,]+/', $value, -1, PREG_SPLIT_NO_EMPTY);
            }

            foreach ($value as $v) {
                $query->orWhereJsonContains($column, $v);
            }
        } else {
            // $query->whereIn(
            //     DB::raw('LOWER("' . $column . '")'),
            //     array_map('strtolower', $value)
            // );
            foreach ($value as $item) {
                $query->where(
                    DB::raw('LOWER("' . $column . '")'),
                    'like',
                    '%' . strtolower($item) . '%'
                );
            }
        }
    }

    /**
     * Apply whereIn on nested relations like store.region.name
     */
    protected function applyNestedRelationWhereIn($query, string $key, array $values)
    {
        $parts = explode('.', $key);
        $column = array_pop($parts); // final column
        $relationPath = $parts;

        $query->whereHas(implode('.', $relationPath), function ($q) use ($column, $values) {
            $this->applyWhereIn($q, $column, $values);
        });
    }

    public function scopeFullSearch($query, ?string $term, array $searchables = [])
    {
        if (empty($term) || empty($searchables)) {
            return $query;
        }

        $excluded = ['email', 'password'];
        $allowedSearchables = array_values(array_diff($searchables, $excluded));

        if (empty($allowedSearchables)) {
            return $query;
        }

        $joins = [];
        $model = $query->getModel();
        $baseTable = $model->getTable();

        /*
        |--------------------------------------------------------------------------
        | Dynamic Joins (Supports Nested Relations)
        |--------------------------------------------------------------------------
        */

        foreach ($allowedSearchables as $col) {

            if (!str_contains($col, '.')) {
                continue;
            }

            $relations = explode('.', $col);
            $relationColumn = array_pop($relations);

            $currentModel = $model;

            foreach ($relations as $relation) {

                $relationObj = $currentModel->$relation();
                $related = $relationObj->getRelated();
                $relatedTable = $related->getTable();

                if (!in_array($relatedTable, $joins)) {

                    if ($relationObj instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {

                        $foreignKey = $relationObj->getQualifiedForeignKeyName();
                        $ownerKey   = $relationObj->getQualifiedOwnerKeyName();

                        $query->leftJoin($relatedTable, $ownerKey, '=', $foreignKey);
                    } elseif (
                        $relationObj instanceof \Illuminate\Database\Eloquent\Relations\HasOne
                        || $relationObj instanceof \Illuminate\Database\Eloquent\Relations\HasMany
                    ) {

                        $foreignKey = $relationObj->getQualifiedForeignKeyName();
                        $localKey   = $relationObj->getQualifiedParentKeyName();

                        $query->leftJoin($relatedTable, $foreignKey, '=', $localKey);
                    } elseif ($relationObj instanceof \Illuminate\Database\Eloquent\Relations\MorphToMany) {

                        $pivotTable = $relationObj->getTable();
                        $relatedTable = $relationObj->getRelated()->getTable();

                        // Join pivot table to base table
                        $query->leftJoin(
                            $pivotTable,
                            $pivotTable . '.' . $relationObj->getForeignPivotKeyName(),
                            '=', // e.g., assigned_roles.entity_id
                            $currentModel->getTable() . '.' . $currentModel->getKeyName()    // e.g., users.id
                        )->where($pivotTable . '.' . $relationObj->getMorphType(), get_class($currentModel)); // entity_type = User

                        // Join related table to pivot
                        $query->leftJoin(
                            $relatedTable,
                            $relatedTable . '.id',
                            '=', // roles.id
                            $pivotTable . '.' . $relationObj->getRelatedPivotKeyName() // assigned_roles.role_id
                        );
                    }

                    $joins[] = $relatedTable;
                }

                $currentModel = $related;
            }
        }

        $query->select($baseTable . '.*');

        /*
        |--------------------------------------------------------------------------
        | Similarity Search
        |--------------------------------------------------------------------------
        */

        $query->where(function ($q) use ($term, $allowedSearchables, $model) {

            $threshold = (count(explode(' ', $term)) / 100) * 10;

            if ($threshold > 0.9) {
                $threshold = 0.9;
            }

            foreach ($allowedSearchables as $col) {

                if (str_contains($col, '.')) {

                    $relations = explode('.', $col);
                    $relationColumn = array_pop($relations);

                    $currentModel = $model;

                    foreach ($relations as $relation) {
                        $currentModel = $currentModel->$relation()->getRelated();
                    }

                    $relatedTable = $currentModel->getTable();

                    $q->orWhereRaw(
                        "similarity(CAST({$relatedTable}.{$relationColumn} AS TEXT), ?) > {$threshold}",
                        [$term]
                    );

                    // $relatedTable}.{$relationColumn}
                    // CAST({$table}.{$col} AS TEXT)

                } else {

                    $table = $model->getTable();

                    $q->orWhereRaw(
                        "similarity(CAST({$table}.{$col} AS TEXT), ?) > {$threshold}",
                        [$term]
                    );
                }
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Similarity Ranking
        |--------------------------------------------------------------------------
        */

        $similarityColumns = array_map(function ($col) use ($term, $model) {

            if (str_contains($col, '.')) {

                $relations = explode('.', $col);
                $relationColumn = array_pop($relations);

                $currentModel = $model;

                foreach ($relations as $relation) {
                    $currentModel = $currentModel->$relation()->getRelated();
                }

                $relatedTable = $currentModel->getTable();

                return "similarity(CAST({$relatedTable}.{$relationColumn} AS TEXT), ?)";
            } else {

                $table = $model->getTable();
                return "similarity(CAST({$table}.{$col} AS TEXT), ?)";
            }
        }, $allowedSearchables);


        $query->orderByRaw(
            'GREATEST(' . implode(', ', $similarityColumns) . ') DESC',
            array_fill(0, count($similarityColumns), $term)
        );

        return $query;
    }

    private static function concatExpression(array $columns, string $separator = ' '): string
    {
        $exprParts = [];
        $lastIndex = count($columns) - 1;

        foreach ($columns as $i => $col) {
            $exprParts[] = $col;
            if ($i !== $lastIndex) {
                $exprParts[] = "'" . $separator . "'";
            }
        }

        return '(' . implode(' || ', $exprParts) . ')';
    }
}
