<?php

namespace App\Services;

use App\DTO\User\UserDTO;
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

        if (empty($relation)) {
            $relation = ['roles'];
        }

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

        if (empty($payload['office_id']) && !empty($payload['office_ids']) && is_array($payload['office_ids'])) {
            $cleanOfficeIds = array_values(array_unique(array_filter(array_map('intval', $payload['office_ids']))));
            if (count($cleanOfficeIds)) {
                $payload['office_id'] = $cleanOfficeIds[0];
            }
        }

        if (DB::getDriverName() === 'pgsql') {
            $seq = DB::selectOne("SELECT pg_get_serial_sequence('users', 'id') as seq");
            if ($seq && $seq->seq) {
                $maxId = DB::table('users')->max('id') ?? 0;
                DB::statement("SELECT setval('{$seq->seq}', " . ($maxId + 1) . ", false)");
            }
        }

        DB::beginTransaction();
        try {

            $user = $this->user->store($payload);
            $roleIds = isset($payload['role_id']) 
                ? (is_array($payload['role_id']) ? $payload['role_id'] : [$payload['role_id']])
                : [2];
            $user->roles()->sync(array_filter($roleIds));

            $user = $user->fresh(['roles']);
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

    public function delete($id, $relation = []): array
    {
        DB::beginTransaction();
        try {
            $this->user->delete($id);
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

        if (empty($payload['office_id']) && !empty($payload['office_ids']) && is_array($payload['office_ids'])) {
            $cleanOfficeIds = array_values(array_unique(array_filter(array_map('intval', $payload['office_ids']))));
            if (count($cleanOfficeIds)) {
                $payload['office_id'] = $cleanOfficeIds[0];
            }
        }

        DB::beginTransaction();
        try {
            $user = $this->user->find($id);

            $this->user->update($user, $payload);

            if (isset($payload['role_id'])) {
                $roleIds = is_array($payload['role_id']) ? $payload['role_id'] : [$payload['role_id']];
                $user->roles()->sync($roleIds);
            }

            $user->refresh();
            $data = collect([$user]);

            $response = [
                'message' => 'Data updated successfully.',
                'body' => UserDTO::fromCollection($data),
            ];

            DB::commit();
            return $response;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
