<?php

namespace App\Repositories;

use App\Traits\QueryGenerator;
use Carbon\Carbon;
use Silber\Bouncer\Database\Role;
use Silber\Bouncer\BouncerFacade as Bouncer;


class RoleRepository
{
    use QueryGenerator;

    public function index($payload)
    {
        $search = $payload['search'] ?? null;
        $skip = $payload['skip'] ?? null;
        $take = $payload['take'] ?? null;

        $data = Role::withCount('abilities');
        
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
            'body' => $list
        ];
    }

    public function show($id)
    {   
        $data = Role::find($id);
        $data->load('abilities');
        return [
            'message' => 'Showing Data.',
            'body' => $data
        ];
    }

    public function store($payload)
    {
        $data = Role::create($payload);

        $data->abilities()->sync($payload['ability_id']);
        
        return $this->show($data->id);
    }

    public function update($id, $payload)
    {
        $data = Role::find($id);

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
        $data = Role::where('id', $id)->update(['deleted_at' => Carbon::now()]);

        return ['message' => 'Data has successfully deleted.'];
    }
}