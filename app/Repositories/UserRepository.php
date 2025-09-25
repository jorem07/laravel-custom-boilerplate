<?php

namespace App\Repositories;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository
{

    public function index($request)
    {
        $data = User::all();
        $data->load(['roles']);
        
        return $data;
    }

    public function show($id)
    {
        // return Role::find($id);
    }

    public function store($payload)
    {
        $payload['created_by'] = Auth::user()->id;
        
        DB::beginTransaction();

        try {
            $payload['password'] = Hash::make($payload['password']);

            $data = User::create($payload);
            $data->assignRole($payload['role']);

            $data->load('roles');

            DB::commit();
            return $data;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
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