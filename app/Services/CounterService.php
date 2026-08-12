<?php

namespace App\Services;

use App\DTO\Counter\CounterDTO;
use App\DTO\Counter\CounterUserLogDTO;
use App\Events\QueueEvent;
use App\Models\Counter;
use App\Models\CounterUserLog;
use App\Repositories\Contracts\CounterRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CounterService extends BaseService
{
    protected CounterRepositoryInterface $counter;

    protected array $defaultRelations = [
        'user' => ['id', 'first_name', 'last_name'],
        'office_service' => ['id', 'name', 'office_id'],
    ];

    protected array $logRelations = [
        'user' => ['id', 'first_name', 'last_name'],
        'counter' => ['id', 'name'],
    ];

    public function __construct(CounterRepositoryInterface $counter)
    {
        $this->counter = $counter;
    }

    public function index($payload, array $searchable = [], $relation = []): array
    {
        $take = $payload['show'] ?? 10;
        $page = $payload['page'] ?? 1;
        $skip = ($page > 1) ? ($take * ($page - 1)) : 0;
        $order = $payload['sort']['order'] ?? null;
        $sort = $payload['sort']['column'] ?? null;

        if (empty($relation)) {
            $relation = $this->defaultRelations;
        }

        $selected_relation = $this->format($relation);
        $searchable = $this->getSearchable('App\\Models\\Counter', $relation);

        $data = $this->counter->query($payload, $searchable, $selected_relation);

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
            'body' => CounterDTO::fromCollection($list),
            'searchable' => $searchable,
        ];
    }

    public function show($id, $payload = [], $relation = []): array
    {
        $rel = !empty($relation) ? array_keys($relation) : array_keys($this->defaultRelations);
        $counter = $this->counter->find($id);
        $data = collect([$counter ? $counter->load($rel) : null])->filter();

        $message = 'Showing Data.';
        if (!$data->count()) {
            $message = 'No result found.';
        }

        return [
            'message' => $message,
            'body' => CounterDTO::fromCollection($data),
        ];
    }

    public function store($payload, $relation = []): array
    {
        DB::beginTransaction();
        try {
            $counter = $this->counter->store($payload);
            $this->syncUserOfficeForCounter($counter, $payload);
            $rel = !empty($relation) ? array_keys($relation) : array_keys($this->defaultRelations);
            $data = collect([$counter->load($rel)]);

            DB::commit();

            return [
                'message' => 'Data created successfully.',
                'body' => CounterDTO::fromCollection($data),
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
            $id = is_array($payload) ? ($payload['id'] ?? null) : $payload;
            if ($id) {
                $this->closeActiveLogForCounter((int) $id);
            }
            $this->counter->delete($payload);
            DB::commit();

            return [
                'message' => 'Data deleted successfully.',
                'body' => null,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, $payload, $relation = []): array
    {
        DB::beginTransaction();
        try {
            $counter = $this->counter->find($id);
            $this->counter->update($counter, $payload);
            $this->syncUserOfficeForCounter($counter, $payload);
            $rel = !empty($relation) ? array_keys($relation) : array_keys($this->defaultRelations);
            $data = collect([$counter->fresh($rel)]);

            DB::commit();

            return [
                'message' => 'Data updated successfully.',
                'body' => CounterDTO::fromCollection($data),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function syncUserOfficeForCounter(Counter $counter, array $payload = []): void
    {
        $userId = $payload['user_id'] ?? $counter->user_id ?? null;
        if (!$userId) return;

        $officeId = $payload['office_id'] ?? null;
        if (!$officeId && $counter->office_service_id) {
            $officeId = \App\Models\OfficeService::where('id', $counter->office_service_id)->value('office_id');
        }
        if (!$officeId && !empty($counter->service_ids)) {
            $officeId = \App\Models\OfficeService::whereIn('id', $counter->service_ids)->value('office_id');
        }

        if ($officeId) {
            \App\Models\User::where('id', $userId)->update(['office_id' => $officeId]);
        }
    }

    public function active(array $relation = []): array
    {
        $counters = $this->getCounters($relation);

        return [
            'message' => 'Active counters.',
            'body' => CounterDTO::fromCollection($counters),
            'total' => $counters->count(),
        ];
    }

    public function logs(array $relation = []): array
    {
        $logs = $this->getTodayLogs($relation ?: $this->logRelations);

        return [
            'message' => 'Counter login logs for today.',
            'body' => CounterUserLogDTO::fromCollection($logs),
            'total' => $logs->count(),
        ];
    }

    public function login(array $payload, array $relation = []): array
    {
        DB::beginTransaction();
        try {
            $counter = Counter::query()->lockForUpdate()->findOrFail($payload['counter_id']);
            $userId = (int) $payload['user_id'];

            $this->closeActiveLogForCounter($counter->id);
            $this->closeActiveLogForUser($userId);

            $log = CounterUserLog::query()->create([
                'counter_id' => $counter->id,
                'user_id' => $userId,
                'log_in' => Carbon::now(),
            ]);

            $counter->update(['user_id' => $userId]);
            $counter = $counter->fresh(array_keys($relation));

            DB::commit();

            $snapshot = $this->getSnapshot($relation);

            $this->broadcastCounterUpdate('counter_login', 'User logged in to counter.', [
                'counter' => CounterDTO::fromModel($counter)->toArray(),
                'log' => CounterUserLogDTO::fromModel(
                    $log->fresh(array_keys($this->logRelations))
                )->toArray(),
            ], $snapshot);

            return [
                'message' => 'User logged in to counter.',
                'body' => CounterDTO::fromCollection(collect([$counter])),
                'others' => [
                    'log' => CounterUserLogDTO::fromModel(
                        $log->fresh(array_keys($this->logRelations))
                    )->toArray(),
                    'counters' => $snapshot['counters'],
                    'counter_logs' => $snapshot['counter_logs'],
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function logout(array $payload, array $relation = []): array
    {
        DB::beginTransaction();
        try {
            $counter = Counter::query()->lockForUpdate()->findOrFail($payload['counter_id']);

            $closedLog = $this->closeActiveLogForCounter($counter->id);
            $rel = !empty($relation) ? array_keys($relation) : array_keys($this->defaultRelations);
            $counter = $counter->fresh($rel);

            DB::commit();

            $snapshot = $this->getSnapshot($relation);

            $this->broadcastCounterUpdate('counter_logout', 'User logged out from counter.', [
                'counter' => CounterDTO::fromModel($counter)->toArray(),
                'log' => $closedLog
                    ? CounterUserLogDTO::fromModel(
                        $closedLog->fresh(array_keys($this->logRelations))
                    )->toArray()
                    : null,
            ], $snapshot);

            return [
                'message' => 'User logged out from counter.',
                'body' => CounterDTO::fromCollection(collect([$counter])),
                'others' => [
                    'log' => $closedLog
                        ? CounterUserLogDTO::fromModel(
                            $closedLog->fresh(array_keys($this->logRelations))
                        )->toArray()
                        : null,
                    'counters' => $snapshot['counters'],
                    'counter_logs' => $snapshot['counter_logs'],
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getSnapshot(array $relation = []): array
    {
        $counterRelation = $relation ?: $this->defaultRelations;
        $logRelation = $this->logRelations;

        return [
            'counters' => CounterDTO::fromCollection($this->getCounters($counterRelation)),
            'counter_logs' => CounterUserLogDTO::fromCollection($this->getTodayLogs($logRelation)),
        ];
    }

    public function resolveLoggedInUserId(int $counterId, ?int $requestedUserId = null): int
    {
        $counter = Counter::query()->findOrFail($counterId);

        if (!$counter->user_id) {
            throw ValidationException::withMessages([
                'counter_id' => ['No user is logged in to this counter. Please log in first.'],
            ]);
        }

        if ($requestedUserId !== null && (int) $requestedUserId !== (int) $counter->user_id) {
            throw ValidationException::withMessages([
                'user_id' => ['The provided user does not match the user logged in to this counter.'],
            ]);
        }

        return (int) $counter->user_id;
    }

    private function getCounters(array $relation): Collection
    {
        return Counter::query()
            ->with(array_keys($relation))
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }

    private function getTodayLogs(array $relation): Collection
    {
        return CounterUserLog::query()
            ->with(array_keys($relation))
            ->whereDate('log_in', Carbon::today())
            ->orderByDesc('log_in')
            ->orderByDesc('id')
            ->get();
    }

    private function closeActiveLogForCounter(int $counterId): ?CounterUserLog
    {
        $log = CounterUserLog::query()
            ->where('counter_id', $counterId)
            ->whereNull('log_out')
            ->latest('log_in')
            ->first();

        if ($log) {
            $log->update(['log_out' => Carbon::now()]);
        }

        return $log;
    }

    private function closeActiveLogForUser(int $userId): void
    {
        $logs = CounterUserLog::query()
            ->where('user_id', $userId)
            ->whereNull('log_out')
            ->get();

        foreach ($logs as $log) {
            $log->update(['log_out' => Carbon::now()]);
        }
    }

    private function broadcastCounterUpdate(
        string $action,
        string $message,
        array $context,
        array $snapshot
    ): void {
        event(new QueueEvent([
            'action' => $action,
            'message' => $message,
            'body' => $snapshot['counters'],
            'counter_logs' => $snapshot['counter_logs'],
            'context' => $context,
            'total' => count($snapshot['counters']),
        ]));
    }
}
