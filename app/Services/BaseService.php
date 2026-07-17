<?php
namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class BaseService
{

    public static function format(array $relation): array
    {
        return collect($relation)
            ->map(function ($value, $index) {
                return !empty($value)
                    ? $index . ':' . implode(',', $value)
                    : $index;
            })
            ->values()
            ->toArray();
    }

    public function getSearchable($model, $relation)
    {
        $searchable = array_diff(Schema::getColumnListing((new $model)->getTable()), (new $model)->getExcludedColumn());
        
        $relations = [];
        foreach ($relation as $index => $items) {
            $relationPath = explode('.', $index);

            $currentModel = new $model;

            foreach ($relationPath as $rel) {
                if (method_exists($currentModel, $rel)) {
                    $currentModel = $currentModel->$rel()->getRelated();
                } else {
                    $currentModel = null;
                    break;
                }
            }
            
            if ($currentModel) {
                $columns = $items;

                $prefixedColumns = collect($columns)->map(fn($col) => $index . '.' . $col)->toArray();
                $relations = array_merge($relations, $prefixedColumns);
            }
        }
        
        
        return array_merge($searchable, $relations);
    }
}
