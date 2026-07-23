<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\QueueStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QueueEstimatedWaitService
{
    public const BATCH_SIZE = 10;

    public function refreshAll(): void
    {
        $waitingStatusId = $this->resolveStatusId('Waiting');

        $serviceIds = Queue::query()
            ->where('queue_status_id', $waitingStatusId)
            ->whereNull('counter_id')
            ->whereDate('time_start', Carbon::today())
            ->whereNotNull('office_service_id')
            ->distinct()
            ->pluck('office_service_id');

        foreach ($serviceIds as $serviceId) {
            $this->refreshForOfficeService((int) $serviceId);
        }
    }

    public function refreshForOfficeService(int $officeServiceId): void
    {
        $waitingStatusId = $this->resolveStatusId('Waiting');
        $completedBatchAverages = $this->getCompletedBatchAverages($officeServiceId);

        $waitingQueues = Queue::query()
            ->where('queue_status_id', $waitingStatusId)
            ->whereNull('counter_id')
            ->where('office_service_id', $officeServiceId)
            ->whereDate('time_start', Carbon::today())
            ->orderBy('time_start')
            ->orderBy('id')
            ->get();

        DB::transaction(function () use ($waitingQueues, $completedBatchAverages) {
            foreach ($waitingQueues as $queue) {
                $position = $this->resolveDailySequence($queue);
                $estimate = $this->calculateEstimate($position, $completedBatchAverages);

                $queue->update([
                    'estimated_wait_minutes' => $estimate['minutes'],
                    'estimated_time_return' => $estimate['return_at'],
                ]);
            }
        });

        $this->clearEstimatesForNonWaitingQueues($officeServiceId);
    }

    /**
     * Position is the daily ticket sequence (e.g. A-019 → 19), not index among waiting only.
     * Batch 1 (tickets 1-10): no estimate.
     * Batch 2+ (11-20, 21-30, ...): slot × avg of previous completed ticket batch.
     */
    private function calculateEstimate(int $position, array $completedBatchAverages): array
    {
        if ($position <= 0) {
            return $this->nullEstimate();
        }

        $batchIndex = (int) ceil($position / self::BATCH_SIZE);

        if ($batchIndex === 1) {
            return $this->nullEstimate();
        }

        $slotInBatch = (($position - 1) % self::BATCH_SIZE) + 1;
        $arrayIndex = $batchIndex - 2;

        if (!isset($completedBatchAverages[$arrayIndex])) {
            return $this->nullEstimate();
        }

        $waitMinutes = (int) round($slotInBatch * $completedBatchAverages[$arrayIndex]);

        return [
            'minutes' => $waitMinutes,
            'return_at' => Carbon::now()->addMinutes($waitMinutes),
        ];
    }

    /**
     * Averages for completed ticket batches: 1-10, 11-20, 21-30, ...
     * Each batch requires all 10 tickets in the range to be completed today.
     *
     * @return array<int, float>
     */
    private function getCompletedBatchAverages(int $officeServiceId): array
    {
        $completedId = $this->resolveStatusId('Completed');

        $completed = Queue::query()
            ->where('office_service_id', $officeServiceId)
            ->where('queue_status_id', $completedId)
            ->whereDate('time_start', Carbon::today())
            ->whereNotNull('time_end')
            ->get();

        $averages = [];
        $batchNumber = 1;

        while (true) {
            $rangeStart = (($batchNumber - 1) * self::BATCH_SIZE) + 1;
            $rangeEnd = $batchNumber * self::BATCH_SIZE;

            $batchQueues = $completed->filter(function (Queue $queue) use ($rangeStart, $rangeEnd) {
                $sequence = $this->resolveDailySequence($queue);

                return $sequence >= $rangeStart && $sequence <= $rangeEnd;
            })->values();

            if ($batchQueues->count() < self::BATCH_SIZE) {
                break;
            }

            $averages[] = $this->averageServiceMinutes($batchQueues);
            $batchNumber++;
        }

        return $averages;
    }

    private function resolveDailySequence(Queue $queue): int
    {
        if (preg_match('/-(\d+)$/', $queue->queue_no, $matches)) {
            return (int) $matches[1];
        }

        return (int) Queue::query()
            ->where('office_service_id', $queue->office_service_id)
            ->whereDate('time_start', Carbon::today())
            ->where(function ($query) use ($queue) {
                $query->where('time_start', '<', $queue->time_start)
                    ->orWhere(function ($inner) use ($queue) {
                        $inner->where('time_start', $queue->time_start)
                            ->where('id', '<=', $queue->id);
                    });
            })
            ->count();
    }

    private function averageServiceMinutes(Collection $queues): float
    {
        $totalMinutes = $queues->sum(function (Queue $queue) {
            return max(
                1,
                Carbon::parse($queue->time_start)->diffInMinutes(Carbon::parse($queue->time_end))
            );
        });

        return round($totalMinutes / self::BATCH_SIZE, 2);
    }

    private function nullEstimate(): array
    {
        return [
            'minutes' => null,
            'return_at' => null,
        ];
    }

    private function clearEstimatesForNonWaitingQueues(int $officeServiceId): void
    {
        $waitingStatusId = $this->resolveStatusId('Waiting');

        Queue::query()
            ->where('office_service_id', $officeServiceId)
            ->whereDate('time_start', Carbon::today())
            ->where(function ($query) use ($waitingStatusId) {
                $query->where('queue_status_id', '!=', $waitingStatusId)
                    ->orWhereNotNull('counter_id');
            })
            ->update([
                'estimated_wait_minutes' => null,
                'estimated_time_return' => null,
            ]);
    }

    private function resolveStatusId(string $name): int
    {
        return QueueStatus::query()->firstOrCreate(['name' => $name])->id;
    }
}
