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
    private const STATUS_SKIPPED = 'Skipped';
    private const STATUS_CANCELLED = 'Cancelled';

    protected QueueRepositoryInterface $queue;

    protected CounterService $counterService;

    protected CounterPerformanceService $counterPerformanceService;

    public function __construct(
        QueueRepositoryInterface $queue,
        CounterService $counterService,
        CounterPerformanceService $counterPerformanceService,
    ) {
        $this->queue = $queue;
        $this->counterService = $counterService;
        $this->counterPerformanceService = $counterPerformanceService;
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

        if (!empty($payload['office_id'])) {
            $officeId = (int) $payload['office_id'];
            $data->where(function ($q) use ($officeId) {
                $q->where('office_id', $officeId)
                  ->orWhereHas('office_service', fn ($sub) => $sub->where('office_id', $officeId));
            });
        }

        $total = $data->count();

        $list = $data->with(['office_service', 'queue_status', 'counter', 'user'])
            ->skip($skip)
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

    private function generateQueueNo(int $serviceId): string
    {
        $serviceRow = DB::table('office_services')
            ->where('id', $serviceId)
            ->select('code', 'name')
            ->first();

        $prefix = 'Q';
        if ($serviceRow) {
            if (!empty($serviceRow->code)) {
                $prefix = strtoupper(trim($serviceRow->code));
            } else {
                $words = explode(' ', trim($serviceRow->name));
                if (count($words) >= 2) {
                    $prefix = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                } else {
                    $prefix = strtoupper(substr($serviceRow->name, 0, 2));
                }
            }
        }

        $today = Carbon::today();

        $lastQueueNo = DB::table('queues')
            ->where('queue_no', 'like', $prefix . '-%')
            ->whereDate('created_at', $today)
            ->orderByDesc('id')
            ->value('queue_no');

        if ($lastQueueNo) {
            $parts = explode('-', $lastQueueNo);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function current(array $relation = []): array
    {
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
                ]);

                $this->counterPerformanceService->recordCompletion($servingQueue->fresh());
            }

            $nextQuery = Queue::query()
                ->where('queue_status_id', $waitingId)
                ->whereDate('created_at', Carbon::now()->format('Y-m-d'))
                ->where(function ($q) use ($counter) {
                    $q->whereNull('counter_id')
                      ->orWhere('counter_id', $counter->id);
                });

            if (!empty($payload['service_ids']) && is_array($payload['service_ids'])) {
                $nextQuery->whereIn('office_service_id', array_map('intval', $payload['service_ids']));
            } elseif ($counter->office_service_id) {
                $nextQuery->where('office_service_id', $counter->office_service_id);
            }

            $nextQueue = $nextQuery
                ->orderBy('time_start')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (!$nextQueue) {
                DB::commit();

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
            ]);

            $called = $nextQueue->fresh(['queue_status', 'counter', 'user']);

            DB::commit();

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

    public function complete(array $payload, array $relation = []): array
    {
        DB::beginTransaction();
        try {
            $completedId = $this->resolveStatusId(self::STATUS_COMPLETED);
            $queue = Queue::findOrFail($payload['queue_id']);

            $queue->update([
                'queue_status_id' => $completedId,
                'counter_id' => $payload['counter_id'],
                'time_end' => Carbon::now(),
            ]);

            $this->counterPerformanceService->recordCompletion($queue->fresh());

            DB::commit();

            $queues = $this->getActiveQueues($relation);
            $snapshot = $this->counterService->getSnapshot();

            $this->broadcastQueueUpdate('complete', QueueDTO::fromModel($queue)->toArray(), $relation, 'Queue completed.');

            return [
                'message' => 'Queue marked as completed.',
                'body' => QueueDTO::fromCollection($queues),
                'total' => $queues->count(),
                'others' => $snapshot,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function skip(array $payload, array $relation = []): array
    {
        DB::beginTransaction();
        try {
            $statusName = !empty($payload['add_to_no_show_list']) ? self::STATUS_CANCELLED : self::STATUS_SKIPPED;
            $statusId = $this->resolveStatusId($statusName);
            $queue = Queue::findOrFail($payload['queue_id']);

            $queue->update([
                'queue_status_id' => $statusId,
                'counter_id' => $payload['counter_id'],
                'time_end' => Carbon::now(),
            ]);

            DB::commit();

            $queues = $this->getActiveQueues($relation);
            $snapshot = $this->counterService->getSnapshot();

            $this->broadcastQueueUpdate('skip', QueueDTO::fromModel($queue)->toArray(), $relation, 'Queue skipped.');

            return [
                'message' => 'Queue skipped.',
                'body' => QueueDTO::fromCollection($queues),
                'total' => $queues->count(),
                'others' => $snapshot,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function recall(array $payload, array $relation = []): array
    {
        DB::beginTransaction();
        try {
            $queue = Queue::findOrFail($payload['queue_id']);
            $mode = $payload['mode'] ?? 'immediate';

            if ($mode === 'requeue' || $mode === 'restore_waiting') {
                $statusId = $this->resolveStatusId(self::STATUS_WAITING);
                $queue->update([
                    'queue_status_id' => $statusId,
                    'counter_id' => null,
                    'time_end' => null,
                ]);
            } else {
                $statusId = $this->resolveStatusId(self::STATUS_SERVING);
                $queue->update([
                    'queue_status_id' => $statusId,
                    'counter_id' => $payload['counter_id'],
                    'time_end' => null,
                ]);
            }

            DB::commit();

            $queues = $this->getActiveQueues($relation);
            $snapshot = $this->counterService->getSnapshot();

            $this->broadcastQueueUpdate('recall', QueueDTO::fromModel($queue)->toArray(), $relation, 'Queue recalled.');

            return [
                'message' => 'Queue recalled.',
                'body' => QueueDTO::fromCollection($queues),
                'total' => $queues->count(),
                'others' => $snapshot,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function transfer(array $payload, array $relation = []): array
    {
        DB::beginTransaction();
        try {
            $queue = Queue::findOrFail($payload['queue_id']);
            $waitingId = $this->resolveStatusId(self::STATUS_WAITING);

            $remarks = $payload['reason'] ?? $payload['remarks'] ?? $queue->remarks;
            $sourceCounterId = $payload['counter_id'] ?? $queue->counter_id;

            $queue->update([
                'transferred_from_counter_id' => $sourceCounterId,
                'counter_id'                  => $payload['target_counter_id'],
                'queue_status_id'             => $waitingId,
                'remarks'                     => $remarks,
            ]);

            DB::commit();

            $freshQueue = $queue->fresh(['office_service', 'queue_status', 'transferred_from_counter']);

            $queues = $this->getActiveQueues($relation);
            $snapshot = $this->counterService->getSnapshot();

            $this->broadcastQueueUpdate('transfer', QueueDTO::fromModel($freshQueue)->toArray(), $relation, 'Queue transferred.');

            return [
                'message' => 'Queue transferred successfully.',
                'body'    => QueueDTO::fromCollection($queues),
                'total'   => $queues->count(),
                'others'  => $snapshot,
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

        $with = array_unique(array_merge($this->mapRelationsForEagerLoad($relation), ['office_service', 'queue_status', 'transferred_from_counter']));

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
        $status = QueueStatus::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();

        if ($status) {
            return $status->id;
        }

        $fallbackMap = [
            'waiting' => 1,
            'serving' => 2,
            'completed' => 3,
            'cancelled' => 4,
            'pending' => 5,
            'skipped' => 6,
        ];

        return $fallbackMap[strtolower($name)] ?? 1;
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
