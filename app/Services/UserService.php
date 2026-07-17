<?php

namespace App\Services;

use App\DTO\User\UserDTO;
use App\Events\TestingEvent;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    protected UserRepositoryInterface $user;

    public function __construct(UserRepositoryInterface $user)
    {
        $this->user = $user;
    }

    public function index($payload, array $searchable = [], $relation = []): array
    {
        $take = $payload['show'] ?? 10;
        $page = $payload['page'] ?? 1;
        $skip = ($page > 1) ? ($take * ($page - 1)) : 0;
        $order = $payload['sort']['order'] ?? null;
        $sort = $payload['sort']['column'] ?? null;


        $selected_relation = $this->format($relation);
        $searchable = $this->getSearchable('App\\Models\\User', $relation);

        $data = $this->user->query($payload, $searchable, $selected_relation);

        $total = $data->count();

        $list = $data->skip($skip)
            ->take($take)
            ->when(isset($payload['sort']), function ($q) use ($sort, $order) {
                $q->orderBy($sort, $order);
            })
            ->get();


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
            'body' => UserDTO::fromCollection($list),
            'searchable' => $searchable
        ];
    }

    public function show($id, $payload = [], $relation = []): array
    {
        $data = collect([$this->user->find($id)]);

        $message = 'Showing Data.';
        if (!$data) {
            $message = 'No result found.';
        }

        return [
            'message' => $message,
            'body' => UserDTO::fromCollection($data)
        ];
    }

    public function store($payload, $relation = []): array
    {
        if (isset($payload['password'])) $payload['password'] = Hash::make($payload['password']);

        DB::beginTransaction();
        try {

            $user = $this->user->store($payload);
            $user->assign($payload['role_id']);

            $data = collect([$user]);

            DB::commit();
            return [
                'message' => 'Data created successfully.',
                'body' => UserDTO::fromCollection($data)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($payload, $relation = []): array
    {
        DB::beginTransaction();
        try {
            $this->user->delete($payload);
            DB::commit();
            return [
                'message' => 'Data deleted successfully.',
                'body' => null
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    } 

    public function update($id, $payload, $relation) : array
    {
        if (isset($payload['password'])) $payload['password'] = Hash::make($payload['password']);

        DB::beginTransaction();
        try {
            $user = $this->user->find($id);

            $this->user->update($user, $payload);

            $user->roles()->sync($payload['role_id']);

            $user->refresh();
            $data = collect([$user]);

            $response = [
                'message' => 'Data updated successfully.',
                'body' => UserDTO::fromCollection($data),
            ];

            // event(new TestingEvent($response));

            DB::commit();
            return $response;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
