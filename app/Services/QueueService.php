<?php

namespace App\Services;

use App\DTO\Queue\QueueDTO;
use App\Events\QueueEvent;
use App\Models\Counter;
use App\Models\Queue;
use App\Models\QueueStatus;
use App\Repositories\Contracts\QueueRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QueueService extends BaseService
{
    private const STATUS_WAITING = 'Waiting';
    private const STATUS_SERVING = 'Serving';
    private const STATUS_COMPLETED = 'Completed';

    protected QueueRepositoryInterface $queue;

    protected CounterService $counterService;

    protected CounterPerformanceService $counterPerformanceService;

    protected QueueEstimatedWaitService $queueEstimatedWaitService;

    public function __construct(
        QueueRepositoryInterface $queue,
        CounterService $counterService,
        CounterPerformanceService $counterPerformanceService,
        QueueEstimatedWaitService $queueEstimatedWaitService,
    ) {
        $this->queue = $queue;
        $this->counterService = $counterService;
        $this->counterPerformanceService = $counterPerformanceService;
        $this->queueEstimatedWaitService = $queueEstimatedWaitService;
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
        $queue = $this->queue->find($id);
        $data = collect($this->index(['search'=>[['key'=>'id', 'value' => $queue->id]]], [], $relation));
        
        $message = 'Showing Data.';
        if (!$data) {
            $message = 'No result found.';
        }

        return [
            'message' => $message,
            'body' => $data['body']
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

            $this->queueEstimatedWaitService->refreshForOfficeService((int) $payload['office_service_id']);
            $this->broadcastQueueUpdate('created', null, $relation);

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

    public function current(array $relation = []): array
    {
        $this->queueEstimatedWaitService->refreshAll();

        $queues = $this->getActiveQueues($relation);
        $snapshot = $this->counterService->getSnapshot();
        $performances = $this->counterPerformanceService->getTodaySnapshot();

        return [
            'message' => 'Current queue list.',
            'body' => QueueDTO::fromCollection($queues),
            'total' => $queues->count(),
            'others' => array_merge($snapshot, [
                'counter_performances' => $performances,
            ]),
        ];
    }

    public function next(array $payload, array $relation = []): array
    {
        $counter = Counter::with(['office_service'])->findOrFail($payload['counter_id']);
        $userId = $this->counterService->resolveLoggedInUserId(
            $counter->id,
            isset($payload['user_id']) ? (int) $payload['user_id'] : null
        );
        
        DB::beginTransaction();
        try {
            $waitingId = $this->resolveStatusId(self::STATUS_WAITING);
            $servingId = $this->resolveStatusId(self::STATUS_SERVING);
            $completedId = $this->resolveStatusId(self::STATUS_COMPLETED);

            $servingQueues = Queue::query()
                ->where('counter_id', $counter->id)
                ->where('queue_status_id', $servingId)
                ->get();
            
            foreach ($servingQueues as $servingQueue) {
                $servingQueue->update([
                    'queue_status_id' => $completedId,
                    'time_end' => Carbon::now(),
                    'estimated_wait_minutes' => null,
                    'estimated_time_return' => null,
                ]);

                $this->counterPerformanceService->recordCompletion($servingQueue->fresh());
            }

            $nextQuery = Queue::query()
                ->where('queue_status_id', $waitingId)
                ->where('office_service_id', $counter->office_service_id)
                ->whereDate('created_at', Carbon::now()->format('Y-m-d'))
                ->whereNull('counter_id');

            if ($counter->office_service_id) {
                $nextQuery->where('office_service_id', $counter->office_service_id);
            }

            $nextQueue = $nextQuery
                ->orderBy('time_start')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (!$nextQueue) {
                DB::commit();

                if ($counter->office_service_id) {
                    $this->queueEstimatedWaitService->refreshForOfficeService((int) $counter->office_service_id);
                }

                $queues = $this->getActiveQueues($relation);
                $snapshot = $this->counterService->getSnapshot();
                $response = [
                    'message' => 'No waiting queue available.',
                    'body' => QueueDTO::fromCollection($queues),
                    'total' => $queues->count(),
                    'others' => array_merge($snapshot, [
                        'counter_performances' => $this->counterPerformanceService->getTodaySnapshot(
                            $counter->office_service_id
                        ),
                    ]),
                ];

                $this->broadcastQueueUpdate('next', null, $relation, $response['message']);

                return $response;
            }

            $nextQueue->update([
                'counter_id' => $counter->id,
                'user_id' => $userId,
                'queue_status_id' => $servingId,
                'estimated_wait_minutes' => null,
                'estimated_time_return' => null,
            ]);

            $called = $nextQueue->fresh(['queue_status', 'counter', 'user']);

            DB::commit();

            if ($counter->office_service_id) {
                $this->queueEstimatedWaitService->refreshForOfficeService((int) $counter->office_service_id);
            } else {
                $this->queueEstimatedWaitService->refreshAll();
            }

            $queues = $this->getActiveQueues($relation);
            $calledDto = QueueDTO::fromModel($called)->toArray();
            $snapshot = $this->counterService->getSnapshot();

            $this->broadcastQueueUpdate('next', $calledDto, $relation, 'Next queue called.');

            return [
                'message' => 'Next queue called.',
                'body' => QueueDTO::fromCollection($queues),
                'total' => $queues->count(),
                'others' => array_merge($snapshot, [
                    'called' => $calledDto,
                    'counter_performances' => $this->counterPerformanceService->getTodaySnapshot(
                        $counter->office_service_id
                    ),
                ]),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function getActiveQueues(array $relation = []): Collection
    {
        $waitingId = $this->resolveStatusId(self::STATUS_WAITING);
        $servingId = $this->resolveStatusId(self::STATUS_SERVING);

        $with = $this->mapRelationsForEagerLoad($relation);

        return Queue::query()
            ->with($with)
            ->whereIn('queue_status_id', [$waitingId, $servingId])
            ->whereDate('time_start', Carbon::today())
            ->orderBy('time_start')
            ->orderBy('id')
            ->get();
    }

    private function mapRelationsForEagerLoad(array $relation): array
    {
        return array_keys($relation);
    }

    private function resolveStatusId(string $name): int
    {
        return QueueStatus::query()->firstOrCreate(['name' => $name])->id;
    }

    private function broadcastQueueUpdate(
        string $action,
        ?array $called,
        array $relation,
        ?string $message = null
    ): void {
        $queues = $this->getActiveQueues($relation);
        $snapshot = $this->counterService->getSnapshot();
        $performances = $this->counterPerformanceService->getTodaySnapshot();

        event(new QueueEvent([
            'action' => $action,
            'message' => $message ?? 'Queue list updated.',
            'called' => $called,
            'body' => QueueDTO::fromCollection($queues),
            'counters' => $snapshot['counters'],
            'counter_logs' => $snapshot['counter_logs'],
            'counter_performances' => $performances,
            'total' => $queues->count(),
        ]));
    }
}
