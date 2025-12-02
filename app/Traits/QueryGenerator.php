<?php

namespace App\Traits;

use Carbon\Carbon;

trait QueryGenerator
{
    public function index($payload = [], array $searchable = [], $relation = []) : array
    {
        $take = $payload['show'] ?? 10;
        $page = $payload['page'] ?? 1;
        $skip = ($page > 1) ? ($take * ($page - 1)) : 0;

        $order = $payload['sort']['order'] ?? null;
        $sort = $payload['sort']['column'] ?? null;

        $search = $payload['search'] ?? [];

        // $search = $this->ignoredSearchable($search, $searchable);

        $full_search = $payload['full_search'] ?? null;

        $selected_relation = collect($relation)->map(function($value, $index) {
            return !empty($value)
                ? $index . ':' . implode(',', $value)
                : $index;
        })->values()->toArray();

        $data = $this->model->with($selected_relation)->newQuery();
        $data->searchColumns($search);
        $data->fullSearch($full_search, $searchable);

        $total = $data->count();
        $list = $data->skip($skip)
                     ->take($take)
                     ->when(isset($payload['sort']), function($q) use ($sort, $order) {
                         $q->orderBy($sort, $order);
                     })
                     ->get();

        if($list->isEmpty() && !isset($payload['show'])){
            return [
                'message' => 'No results found.',
                'error' => null,
                'body' => [],
                'total' => 0
            ];
        }

        return [
            'message' => 'These are the results.',
            'error' => null,
            'current_page' => $take > 0 ? intval($skip / $take) + 1 : 1,
            'from' => $skip + 1,
            'to' => min(($skip + $take), $total),
            'last_page' => ($take > 0) ? ceil($total / $take) : 1,
            'skip' => $skip,
            'take' => $take,
            'total' => $total,
            'body' => $list,
            'searchable' => !empty($searchable) ? $searchable : null
        ];
    }

    public function show($id, $payload = [], $relation = []) : array
    {
        $data = $this->index(['search' => [['key' => 'id', 'value' => $id,]]], [], $relation);
        
        $data = $data['body'] ?? null;
        $message = 'Showing Data.';
        if(!$data){
            $message = 'No result found.';
        }

        return [
            'message' => $message,
            'body' => $data
        ];
    }

    public function store($payload, $relation = []) : array
    {

        $data = $this->model->create($payload);
        $data = $this->index(['search' => [['key' => 'id', 'value' => $data->id,]]], [], $relation);
        $data = $data['body'];
 
        return [
            'message' => 'Sucessfully created the data.',
            'body'    => $data
        ];
    }

    public function update($id, $payload, $relation = []) : array
    {
        $data = $this->model->find($id);

        $data->update($payload);

        $data = $this->index(['search' => [['key' => 'id', 'value' => $id,]]], [], $relation);
        $data = $data['body'];

        return [
            'message' => 'Sucessfully updated the data.',
            'body'    => $data
        ];
    }

    public function delete($id)
    {
        $data = $this->model->where('id', $id)->update(['deleted_at' => Carbon::now()]);

        return ['message' => 'Data has successfully deleted.'];
    }

   public function ignoredSearchable(array $search, array $searchable): array
    {
        if (empty($searchable)) {
            return [];
        }
        
        return array_filter($search, function ($item) use ($searchable) {
            return in_array($item['key'] ?? null, $searchable);
        });
    }

}
