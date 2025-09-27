<?php

namespace App\Repositories;

use App\Models\Role;
use App\Traits\QueryGenerator;
use Carbon\Carbon;

class RoleRepository
{
    use QueryGenerator;
    
    protected Role $model;

    public function __construct(Role $model)
    {
        $this->model = $model;
    }

    public function index($payload, array $searchable = [])
    {
        $search = $payload['search'] ?? [];
        $full_search = $payload['full_search'] ?? null;


        $skip = $payload['skip'] ?? null;
        $take = $payload['take'] ?? null;

        $data = $this->model->withCount('abilities')->newQuery();

        $data->searchColumns($search);
        $data->fullSearch($full_search, $searchable);
        
        $total = $data->count();
        $list = $data->skip($skip)->take($take)->get();
        
        
        return [
            'message' => 'These are the results.',
            'error' => null,
            'current_page' => $take > 0 ? intval($skip / $take) + 1 : 1,
            'from' => $skip + 1,
            'to' => min(($skip + $take), $total),
            'skip' => $skip,
            'take' => $take,
            'total' => $total,
            'body' => $list,
            'searchable' => $searchable
        ];
    }

    public function show($id)
    {   
        $data = $this->model->find($id);
        $data->load('abilities');
        return [
            'message' => 'Showing Data.',
            'body' => $data
        ];
    }

    public function store($payload)
    {
        $data = $this->model->create($payload);

        $data->abilities()->sync($payload['ability_id']);
        
        return $this->show($data->id);
    }

    public function update($id, $payload)
    {
        $data = $this->model->find($id);

        if(isset($payload['name'])){
            $data->update([
                'name'  => $payload['name'],
                'title' => $payload['title']
            ]);
        }

        if(isset($payload['ability_id'])){
            $data->abilities()->detach();
            $data->abilities()->sync($payload['ability_id']);
        }

        return $this->show($id);
        
    }

    public function delete($id)
    {
        $data = $this->model->where('id', $id)->update(['deleted_at' => Carbon::now()]);

        return ['message' => 'Data has successfully deleted.'];
    }
}