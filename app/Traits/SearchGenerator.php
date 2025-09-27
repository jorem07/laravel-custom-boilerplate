<?php

namespace App\Traits;

trait SearchGenerator
{
    public function scopeSearchColumns($query, array $searches)
    {
        return $query->when(!empty($searches), function ($q) use ($searches) {
            $q->where(function ($sub) use ($searches) {
                foreach ($searches as $search) {
                    if (
                        !empty($search['column']) &&
                        isset($search['condition']) &&
                        $search['condition'] !== ''
                    ) {
                        $column = $search['column'];
                        $value  = strtoupper($search['condition']);

                        $sub->orWhereRaw("UPPER($column) LIKE ?", ["%{$value}%"]);
                    }
                }
            });
        });
    }

    public function scopeFullSearch($query, ?string $term, array $searchables = [])
    {
        if (empty($term) || empty($searchables)) {
            return $query;
        }

        $tokens = explode(' ', $term);

        return $query->where(function ($q) use ($tokens, $searchables) {
            foreach ($tokens as $token) {
                $token = strtoupper($token);
                $q->whereRaw(
                    '(' . implode(' OR ', array_map(fn($col) => "UPPER($col) LIKE ?", $searchables)) . ')',
                    array_fill(0, count($searchables), "%{$token}%")
                );
            }
        });
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
