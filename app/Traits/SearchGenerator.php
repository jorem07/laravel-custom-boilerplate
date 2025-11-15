<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait SearchGenerator
{
    public function scopeSearchColumns($query, array $searches)
    {
        $excluded = ['email','password'];

        $filteredSearches = array_filter($searches, function ($search) use ($excluded) {
            return !in_array($search['key'] ?? '', $excluded, true);
        });

        return $query->when(!empty($filteredSearches), function ($q) use ($filteredSearches) {
            $q->where(function ($sub) use ($filteredSearches) {
                
                foreach ($filteredSearches as $search) {
                    $key = $search['key'] ?? null;
                    $value = $search['value'] ?? null;

                    $between = $search['between'] ?? null;
                    
                    $between = collect($between)->map(function($q){
                        return $q;
                    })
                    ->filter()
                    ->values()
                    ->toArray();

                    if (isset($between) && count($between) === 2) {
                        $sub->whereBetween($key, $between);
                        continue;
                    }
                    // dd($filteredSearches);
                    if (!$key || $value === '' || $value === null) continue;           
                    
                    if (Str::contains($key, '.')) {
                        
                        [$relation, $column] = explode('.', $key, 2);

                        $sub->orWhereHas($relation, function ($rel) use ($column, $value) {
                            $rel->where($column, 'ILIKE', "%{$value}%");
                        });
                    } else {
                        if ($key === 'id' || ctype_digit((string) $value) || is_bool($value)) {
                            $sub->where($key, $value);
                        } else {
                            $sub->orWhere($key, 'ILIKE', "%{$value}%");
                        }
                    }
                }
            });
        });
    }


    // public function scopeFullSearch($query, ?string $term, array $searchables = [])
    // {
    //     if (empty($term) || empty($searchables)) {
    //         return $query;
    //     }

    //     $excluded = ['email', 'password'];
    //     $allowedSearchables = array_values(array_diff($searchables, $excluded));

    //     if (empty($allowedSearchables)) {
    //         return $query;
    //     }

    //     $tokens = explode(' ', $term);

    //     $query->where(function ($q) use ($tokens, $allowedSearchables) {
    //         foreach ($tokens as $token) {
    //             $token = strtoupper($token);

    //             $conditions = [];

    //             foreach ($allowedSearchables as $col) {
    //                 // Handle relation dot notation: e.g. packages.name
    //                 if (str_contains($col, '.')) {
    //                     [$relation, $relationColumn] = explode('.', $col, 2);

    //                     $q->orWhereHas($relation, function ($sub) use ($relationColumn, $token) {
    //                         $sub->whereRaw("UPPER($relationColumn) LIKE ?", ["%{$token}%"]);
    //                     });
    //                 } else {
    //                     // Main table column
    //                     $conditions[] = "UPPER($col) LIKE ?";
    //                 }
    //             }

    //             if (!empty($conditions)) {
    //                 $q->orWhereRaw('(' . implode(' OR ', $conditions) . ')', array_fill(0, count($conditions), "%{$token}%"));
    //             }
    //         }
    //     });

    //     return $query;
    // }

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

        foreach ($allowedSearchables as $col) {
            if (str_contains($col, '.')) {
                [$relation, $relationColumn] = explode('.', $col, 2);
                $relationObj = $query->getModel()->$relation();

                if (method_exists($relationObj, 'getRelated')) {
                    $related = $relationObj->getRelated();
                    $relatedTable = $related->getTable();

                    if ($relationObj instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                        $foreignKey = $relationObj->getQualifiedForeignKeyName();
                        $ownerKey = $relationObj->getQualifiedOwnerKeyName();
                        if (!in_array($relatedTable, $joins)) {
                            $query->leftJoin($relatedTable, $ownerKey, '=', $foreignKey);
                            $joins[] = $relatedTable;
                        }
                    } else {
                        $foreignKey = $relationObj->getQualifiedForeignKeyName();
                        $localKey = $relationObj->getQualifiedParentKeyName();
                        if (!in_array($relatedTable, $joins)) {
                            $query->leftJoin($relatedTable, $foreignKey, '=', $localKey);
                            $joins[] = $relatedTable;
                        }
                    }
                }
            }
        }


        $query->select($query->getModel()->getTable() . '.*');

        $query->where(function ($q) use ($term, $allowedSearchables) {

            $threshold = (count(explode(' ', $term)) / 100) * 10;

            if($threshold > 0.9){
                $threshold = 0.9;
            }

            foreach ($allowedSearchables as $col) {
                if (str_contains($col, '.')) {
                    [$relation, $relationColumn] = explode('.', $col, 2);

                    $relatedTable = $q->getModel()->$relation()->getRelated()->getTable();
                    $q->orWhereRaw("similarity({$relatedTable}.{$relationColumn}, ?) > $threshold", [$term]);
                } else {

                    $table = $q->getModel()->getTable();
                    $q->orWhereRaw("similarity({$table}.{$col}, ?) > $threshold", [$term]);
                }
            }
        });

        $similarityColumns = array_map(function ($col) use ($query) {
            if (str_contains($col, '.')) {
                [$relation, $relationColumn] = explode('.', $col, 2);
                $relatedTable = $query->getModel()->$relation()->getRelated()->getTable();
                return "similarity({$relatedTable}.{$relationColumn}, ?)";
            } else {
                $table = $query->getModel()->getTable();
                return "similarity({$table}.{$col}, ?)";
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
