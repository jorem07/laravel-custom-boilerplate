<?php

namespace App\Services;

use App\DTO\Queue\QueueDTO;
use App\Repositories\Contracts\QueueRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QueueService extends BaseService
{

    protected QueueRepositoryInterface $queue;

    public function __construct(QueueRepositoryInterface $queue)
    {
        $this->queue = $queue;
    }

    public function index($payload, array $searchable = [], $relation = []): array
    {
        $take = $payload['show'] ?? 10;
        $page = $payload['page'] ?? 1;
        $skip = ($page > 1) ? ($take * ($page - 1)) : 0;
        $order = $payload['sort']['order'] ?? null;
        $sort = $payload['sort']['column'] ?? null;


        $selected_relation = $this->format($relation);
        $searchable = $this->getSearchable('App\\Models\\Queue', $relation);

        $data = $this->queue->query($payload, $searchable, $selected_relation);

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
            'body' => QueueDTO::fromCollection($list),
            'searchable' => $searchable
        ];
    }

    public function show($id, $payload = [], $relation = []): array
    {
        $data = collect([$this->queue->find($id)]);

        $message = 'Showing Data.';
        if (!$data) {
            $message = 'No result found.';
        }

        return [
            'message' => $message,
            'body' => QueueDTO::fromCollection($data)
        ];
    }

    public function store($payload, $relation = []): array
    {
        $payload['queue_no'] = $this->generateQueueNo($payload['office_service_id']);

        DB::beginTransaction();
        try {

            $queue = $this->queue->store($payload);

            $data = collect([$queue]);

            DB::commit();
            return [
                'message' => 'Data created successfully.',
                'body' => QueueDTO::fromCollection($data)
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
            $this->queue->delete($payload);
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

    public function update($id, $payload, $relation): array
    {
        DB::beginTransaction();
        try {
            $queue = $this->queue->find($id);

            $this->queue->update($queue, $payload);

            $data = collect([$queue]);

            DB::commit();
            return [
                'message' => 'Data updated successfully.',
                'body' => QueueDTO::fromCollection($data)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function generateQueueNo(int $serviceId): string|null
    {
        $service = DB::table('office_services as os')
            ->join('office_service_categories as osc', 'os.office_service_category_id', '=', 'osc.id')
            ->where('os.id', $serviceId)
            ->value('osc.type');

        if (!$service) {
            return null;
        }

        $prefix = strtoupper($service);
        $today  = Carbon::today();

        $lastQueueNo = DB::table('queues')
            ->where('queue_no', 'like', $prefix . '-%')
            ->whereDate('created_at', $today)
            ->orderByDesc('id')
            ->value('queue_no');

        if ($lastQueueNo) {
            $lastNumber = (int) substr($lastQueueNo, strlen($prefix) + 1);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
