<?php

namespace App\Repositories;

use Silber\Bouncer\Database\Role;
use Silber\Bouncer\BouncerFacade as Bouncer;


class RoleRepository
{
    public function index($request)
    {
        $data = Role::withCount('abilities')->get();
        
        return $data;
    }

    public function show($id)
    {   
        $data = Role::find($id);
        $data->load('abilities');
        return $data;
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
        // return Role::destroy($id);
    }
}