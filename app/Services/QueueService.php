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
use Illuminate\Support\Str;

class QueueService extends BaseService
{
    private const STATUS_WAITING = 'Waiting';
    private const STATUS_SERVING = 'Serving';
    private const STATUS_COMPLETED = 'Completed';
    private const STATUS_CANCELLED = 'Cancelled';

    protected QueueRepositoryInterface $queue;

    protected CounterService $counterService;

    public function __construct(
        QueueRepositoryInterface $queue,
        CounterService $counterService,
    ) {
        $this->queue = $queue;
        $this->counterService = $counterService;
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
        do {
            $code = strtoupper(Str::random(5));
        } while (DB::table('queues')->where('queue_no', $code)->exists());

        return $code;
    }

    public function current(array $relation = []): array
    {
        $queues = $this->getActiveQueues($relation);
        $snapshot = $this->counterService->getSnapshot();

        return [
            'message' => 'Current queue list.',
            'body' => QueueDTO::fromCollection($queues),
            'total' => $queues->count(),
            'others' => $snapshot,
        ];
    }

    public function next(array $payload, array $relation = []): array
    {
        $counter = Counter::findOrFail($payload['counter_id']);
        $userId = $this->counterService->resolveLoggedInUserId(
            $counter->id,
            isset($payload['user_id']) ? (int) $payload['user_id'] : null
        );

        DB::beginTransaction();
        try {
            $waitingId = $this->resolveStatusId(self::STATUS_WAITING);
            $servingId = $this->resolveStatusId(self::STATUS_SERVING);
            $completedId = $this->resolveStatusId(self::STATUS_COMPLETED);

            Queue::query()
                ->where('counter_id', $counter->id)
                ->where('queue_status_id', $servingId)
                ->update([
                    'queue_status_id' => $completedId,
                    'time_end' => Carbon::now(),
                ]);

            $nextQuery = Queue::query()
                ->where('queue_status_id', $waitingId)
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

                $queues = $this->getActiveQueues($relation);
                $snapshot = $this->counterService->getSnapshot();
                $response = [
                    'message' => 'No waiting queue available.',
                    'body' => QueueDTO::fromCollection($queues),
                    'total' => $queues->count(),
                    'others' => $snapshot,
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
                ]),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function complete(array $payload, array $relation = []): array
    {
        $completedId = $this->resolveStatusId(self::STATUS_COMPLETED);
        $servingId = $this->resolveStatusId(self::STATUS_SERVING);

        DB::beginTransaction();
        try {
            $query = Queue::query();

            if (!empty($payload['queue_id'])) {
                $query->where('id', $payload['queue_id']);
            } elseif (!empty($payload['counter_id'])) {
                $query->where('counter_id', $payload['counter_id'])->where('queue_status_id', $servingId);
            }

            $queue = $query->first();
            if ($queue) {
                $queue->update([
                    'queue_status_id' => $completedId,
                    'time_end' => Carbon::now(),
                ]);
            }

            DB::commit();

            $queues = $this->getActiveQueues($relation);
            $snapshot = $this->counterService->getSnapshot();

            $this->broadcastQueueUpdate('complete', $queue ? QueueDTO::fromModel($queue->fresh())->toArray() : null, $relation, 'Ticket completed.');

            return [
                'message' => 'Ticket completed successfully.',
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
        $cancelledId = $this->resolveStatusId(self::STATUS_CANCELLED);
        $servingId = $this->resolveStatusId(self::STATUS_SERVING);

        DB::beginTransaction();
        try {
            $query = Queue::query();

            if (!empty($payload['queue_id'])) {
                $query->where('id', $payload['queue_id']);
            } elseif (!empty($payload['counter_id'])) {
                $query->where('counter_id', $payload['counter_id'])->where('queue_status_id', $servingId);
            }

            $queue = $query->first();
            if ($queue) {
                $queue->update([
                    'queue_status_id' => $cancelledId,
                    'time_end' => Carbon::now(),
                ]);
            }

            DB::commit();

            $queues = $this->getActiveQueues($relation);
            $snapshot = $this->counterService->getSnapshot();

            $this->broadcastQueueUpdate('skip', $queue ? QueueDTO::fromModel($queue->fresh())->toArray() : null, $relation, 'Ticket skipped.');

            return [
                'message' => 'Ticket skipped successfully.',
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
        $servingId = $this->resolveStatusId(self::STATUS_SERVING);
        $query = Queue::query()->where('queue_status_id', $servingId);

        if (!empty($payload['counter_id'])) {
            $query->where('counter_id', $payload['counter_id']);
        }

        $queue = $query->first();
        $calledDto = $queue ? QueueDTO::fromModel($queue)->toArray() : null;

        $queues = $this->getActiveQueues($relation);
        $snapshot = $this->counterService->getSnapshot();

        $this->broadcastQueueUpdate('recall', $calledDto, $relation, 'Ticket recalled.');

        return [
            'message' => 'Ticket recalled.',
            'body' => QueueDTO::fromCollection($queues),
            'total' => $queues->count(),
            'others' => array_merge($snapshot, ['called' => $calledDto]),
        ];
    }

    public function transfer(array $payload, array $relation = []): array
    {
        DB::beginTransaction();
        try {
            if (!empty($payload['queue_id']) && !empty($payload['target_service_id'])) {
                $queue = Queue::find($payload['queue_id']);
                if ($queue) {
                    $queue->update([
                        'office_service_id' => $payload['target_service_id'],
                        'counter_id' => null,
                        'queue_status_id' => $this->resolveStatusId(self::STATUS_WAITING),
                    ]);
                }
            }
            DB::commit();

            $queues = $this->getActiveQueues($relation);
            $snapshot = $this->counterService->getSnapshot();

            $this->broadcastQueueUpdate('transfer', null, $relation, 'Ticket transferred.');

            return [
                'message' => 'Ticket transferred successfully.',
                'body' => QueueDTO::fromCollection($queues),
                'total' => $queues->count(),
                'others' => $snapshot,
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

        event(new QueueEvent([
            'action' => $action,
            'message' => $message ?? 'Queue list updated.',
            'called' => $called,
            'body' => QueueDTO::fromCollection($queues),
            'counters' => $snapshot['counters'],
            'counter_logs' => $snapshot['counter_logs'],
            'total' => $queues->count(),
        ]));
    }
}
