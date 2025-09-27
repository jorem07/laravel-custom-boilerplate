<?php

namespace App\Repositories;

use App\Models\User;
use App\Traits\QueryGenerator;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Silber\Bouncer\BouncerFacade as Bouncer;

class UserRepository
{
    use QueryGenerator;

    protected User $model;
    
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function index($payload, array $searchable = [])
    {
        $status = isset($payload['status'])  
                  ? [$payload['status']] 
                  : [true, false];
        
        $roles = $payload['roles'] ?? [];
        
        $search = $payload['search'] ?? [];
        $full_search = $payload['full_search'] ?? null;

        $skip = $payload['skip'] ?? null;
        $take = $payload['take'] ?? null;
        

        $data = $this->model->with('roles:id,name,title')
            ->whereIn('status', $status)
            ->where(function($q) use ($roles) {
                $q->whereHas('roles', function($sub) use ($roles) {
                    if (!empty($roles)) {
                        $sub->whereIn('name', $roles);
                    }
                    $sub->whereNot('name', 'super-admin');
                });
            })->newQuery();
        
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

        if($data){
            $data->load(['roles']);
        }

        return [
            'message' => 'Showing Data.',
            'body' => $data
        ];
    }

    public function store($payload)
    {
        $payload['created_by'] = Auth::user()->id;
        
        DB::beginTransaction();

        try {
            $payload['password'] = Hash::make($payload['password']);

            $data = $this->model->create($payload);
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
        $data = $this->model->find($id);

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
        $data = $this->model->where('id', $id)->update(['deleted_at' => Carbon::now()]);

        return ['message' => 'Data has successfully deleted.'];
    }
}