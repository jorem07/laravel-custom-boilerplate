<?php

namespace App\Services;

use App\DTO\QueueStatus\QueueStatusDTO;
use App\Repositories\Contracts\QueueStatusRepositoryInterface;
use Illuminate\Support\Facades\DB;

class QueueStatusService extends BaseService
{

    protected QueueStatusRepositoryInterface $queueStatus;

    public function __construct(QueueStatusRepositoryInterface $queueStatus)
    {
        $this->queueStatus = $queueStatus;
    }

    public function index($payload, array $searchable = [], $relation = []): array
    {
        $take = $payload['show'] ?? 10;
        $page = $payload['page'] ?? 1;
        $skip = ($page > 1) ? ($take * ($page - 1)) : 0;
        $order = $payload['sort']['order'] ?? null;
        $sort = $payload['sort']['column'] ?? null;


        $selected_relation = $this->format($relation);
        $searchable = $this->getSearchable('App\\Models\\QueueStatus', $relation);

        $data = $this->queueStatus->query($payload, $searchable, $selected_relation);

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
            'body' => QueueStatusDTO::fromCollection($list),
            'searchable' => $searchable
        ];
    }

    public function show($id, $payload = [], $relation = []): array
    {
        $data = collect([$this->queueStatus->find($id)]);

        $message = 'Showing Data.';
        if (!$data) {
            $message = 'No result found.';
        }

        return [
            'message' => $message,
            'body' => QueueStatusDTO::fromCollection($data)
        ];
    }

    public function store($payload, $relation = []): array
    {
        DB::beginTransaction();
        try {

            $queueStatus = $this->queueStatus->store($payload);

            $data = collect([$queueStatus]);

            DB::commit();
            return [
                'message' => 'Data created successfully.',
                'body' => QueueStatusDTO::fromCollection($data)
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
            $this->queueStatus->delete($payload);
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
        DB::beginTransaction();
        try {
            $queueStatus = $this->queueStatus->find($id);

            $this->queueStatus->update($queueStatus, $payload);

            $data = collect([$queueStatus]);

            DB::commit();
            return [
                'message' => 'Data updated successfully.',
                'body' => QueueStatusDTO::fromCollection($data)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
