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

    public function store($request)
    {
        $data = Role::create($request);

        Bouncer::allow($data)->to('*');
        
        return $data;
    }

    public function update($id, array $data)
    {
        // return Role::where('id', $id)->update($data);
    }

    public function delete($id)
    {
        // return Role::destroy($id);
    }
}