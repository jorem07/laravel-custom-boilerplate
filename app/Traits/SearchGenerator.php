<?php

namespace App\Traits;

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
                    if (
                        !empty($search['key']) &&
                        isset($search['value']) &&
                        $search['value'] !== ''
                    ) {
                        $key = $search['key'];
                        $value  = strtoupper($search['value']);

                        $sub->orWhereRaw("UPPER($key) LIKE ?", ["%{$value}%"]);
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

        $excluded = ['email','password'];
        $allowedSearchables = array_values(array_diff($searchables, $excluded));

        if (empty($allowedSearchables)) {
            return $query;
        }

        $tokens = explode(' ', $term);

        return $query->where(function ($q) use ($tokens, $allowedSearchables) {
            foreach ($tokens as $token) {
                $token = strtoupper($token);
                $q->whereRaw(
                    '(' . implode(' OR ', array_map(fn($col) => "UPPER($col) LIKE ?", $allowedSearchables)) . ')',
                    array_fill(0, count($allowedSearchables), "%{$token}%")
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
