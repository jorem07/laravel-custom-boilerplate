<?php

namespace App\Repositories;

use App\Models\Location;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Builder;

class UserRepository implements UserRepositoryInterface
{
    protected User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function query($payload, $searchable, $selected_relation): Builder
    {
        $roles = $payload['roles'] ?? [];
        $search = $payload['search'] ?? [];
        $full_search = $payload['full_search'] ?? null;

        $status = isset($payload['status'])  ? [$payload['status']] : [true, false];

        $data = $this->model->newQuery()
            ->with($selected_relation)
            ->whereIn('status', $status)
            ->when(!empty($roles), function ($q) use ($roles) {
                $q->where(function ($q) use ($roles) {
                    $q->whereHas('roles', function ($sub) use ($roles) {
                        $sub->whereIn('name', $roles);
                    });
                });
            });

        $searchedIds = null;

        if (isset($full_search)) $searchedIds = (clone $data)->fullSearch($full_search, $searchable)->pluck('id');

        if (isset($searchedIds)) $data->whereIn('id', $searchedIds);

        $data->searchColumns($search);

        return $data;
    }

    public function find($id): User
    {
        return $this->model->find($id);
    }

    public function store(array $payload): User
    {
        return $this->model->create($payload);
    }

    public function update($data, $payload): User
    {
        $data->update($payload);
        return $data;
    }

    public function delete($id): bool
    {
        return $this->model->where('id', $id)->update(['deleted_at' => \Carbon\Carbon::now()]);
    }
}
