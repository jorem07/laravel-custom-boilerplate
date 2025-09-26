<?php

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Silber\Bouncer\BouncerFacade as Bouncer;

class UserRepository
{

    public function index($payload)
    {
        $status = isset($payload['status'])  
                  ? [$payload['status']] 
                  : [true, false];
        
        $roles = $payload['roles'] ?? [];

        $data = User::with('roles:id,name,title')
            ->whereIn('status', $status)
            ->where(function($q) use ($roles) {
                $q->whereHas('roles', function($sub) use ($roles) {
                    if (!empty($roles)) {
                        $sub->whereIn('name', $roles);
                    }
                    $sub->whereNot('name', 'super-admin');
                });
            })
            ->get();

        
        return $data;
    }

    public function show($id)
    {
        $data = User::find($id);

        if($data){
            $data->load(['roles']);
        }

        return $data;
    }

    public function store($payload)
    {
        $payload['created_by'] = Auth::user()->id;
        
        DB::beginTransaction();

        try {
            $payload['password'] = Hash::make($payload['password']);

            $data = User::create($payload);
            $data->assign($payload['role']);


            DB::commit();
            return $this->show($data->id);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, $payload)
    {
        if(isset($payload['password'])){
            $payload['password'] = Hash::make($payload['password']);
        }
        $data = User::find($id);

        $role = $payload['role'] ?? null;
        unset($payload['role']);

        $data->update($payload);
        
        if($role){
            $data->roles()->detach();

            // assign the new role
            Bouncer::assign($role)->to($data);
        }

        return $this->show($id);
        
    }

    public function delete($id)
    {
        $data = User::where('id', $id)->update(['deleted_at' => Carbon::now()]);

        return ['message' => 'Data has successfully deleted.'];
    }
}