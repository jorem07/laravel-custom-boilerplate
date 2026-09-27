<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait RepositoryTrait
{

    public function query($payload, $searchable, $selected_relation): Builder
    {
        $search = $payload['search'] ?? [];
        $full_search = $payload['full_search'] ?? null;
        
        $data = $this->model->newQuery()->with($selected_relation);
        
        $searchedIds = null;

        if(isset($full_search)) $searchedIds = (clone $data)->fullSearch($full_search, $searchable)->pluck('id');

        if(isset($searchedIds)) $data->whereIn('id', $searchedIds);
            
        if (method_exists($data, 'searchColumns') || (method_exists($data, 'hasMacro') && $data->hasMacro('searchColumns'))) {
            $data->searchColumns($search);
        }

        return $data;
    }
    
    public function find($id): Model
    {
        return $this->model->find($id);
    }

    public function store($payload): Model
    {
        return $this->model->create($payload);
    }

    public function update($data, $payload): Model
    {
        $data->update($payload);

        return $data;
    }

    public function delete($id): bool
    {
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        return $this->model->where('id', $targetId)->delete();
    }
    

    public function ignoredSearchable(array $search, array $searchable): array
     {
        if (empty($searchable)) {
               return [];
        }
         return array_filter($search, function ($item) use ($searchable) {
            if(strpos($item['key'], 'id') || $item['key'] == 'id'){
                array_push($searchable, $item['key']);
            }
             return in_array($item['key'] ?? null, $searchable);
        });
        
    }

}
