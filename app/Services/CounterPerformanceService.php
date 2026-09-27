<?php

namespace App\Services;

use App\DTO\Counter\CounterPerformanceDTO;
use App\Models\Counter;
use App\Models\CounterPerformance;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CounterPerformanceService
{
    public const DEFAULT_AVG_MINUTES = 12;

    public function recordCompletion(Queue $queue): void
    {
        if (!$queue->counter_id || !$queue->time_end) {
            return;
        }

        $counter = Counter::query()->find($queue->counter_id);
        $serviceMinutes = max(
            1,
            Carbon::parse($queue->time_start)->diffInMinutes(Carbon::parse($queue->time_end))
        );

        $performance = CounterPerformance::query()->firstOrCreate(
            [
                'counter_id' => $queue->counter_id,
                'performance_date' => Carbon::today(),
            ],
            [
                'office_service_id' => $counter?->office_service_id ?? $queue->office_service_id,
                'served_count' => 0,
                'total_service_minutes' => 0,
            ]
        );

        $performance->office_service_id = $counter?->office_service_id ?? $queue->office_service_id;
        $performance->served_count++;
        $performance->total_service_minutes += $serviceMinutes;
        $performance->avg_time_minutes = round(
            $performance->total_service_minutes / $performance->served_count,
            2
        );
        $performance->save();

        $this->recalculateEfficiency($performance->office_service_id, Carbon::today());
    }

    public function recalculateEfficiency(?int $officeServiceId, Carbon $date): void
    {
        $query = CounterPerformance::query()->whereDate('performance_date', $date);

        if ($officeServiceId) {
            $query->where('office_service_id', $officeServiceId);
        }

        $performances = $query->get();

        if ($performances->isEmpty()) {
            return;
        }

        $active = $performances->where('served_count', '>', 0);

        if ($active->isEmpty()) {
            return;
        }

        $maxServed = max(1, (int) $active->max('served_count'));
        $minAvg = max(1, (float) $active->min('avg_time_minutes'));

        foreach ($performances as $performance) {
            if ($performance->served_count <= 0 || !$performance->avg_time_minutes) {
                $performance->update(['efficiency_percent' => 0]);
                continue;
            }

            $servedScore = ($performance->served_count / $maxServed) * 50;
            $speedScore = ($minAvg / $performance->avg_time_minutes) * 50;
            $efficiency = min(100, round($servedScore + $speedScore, 2));

            $performance->update(['efficiency_percent' => $efficiency]);
        }
    }

    public function getAverageServiceMinutes(?int $officeServiceId): float
    {
        $query = CounterPerformance::query()
            ->whereDate('performance_date', Carbon::today())
            ->where('served_count', '>', 0);

        if ($officeServiceId) {
            $query->where('office_service_id', $officeServiceId);
        }

        $avg = $query->avg('avg_time_minutes');

        return $avg ? (float) $avg : self::DEFAULT_AVG_MINUTES;
    }

    public function getTopPerformances(?int $officeServiceId = null, int $limit = 5): array
    {
        $query = CounterPerformance::query()
            ->with(['counter:id,name'])
            ->whereDate('performance_date', Carbon::today())
            ->where('served_count', '>', 0);

        if ($officeServiceId) {
            $query->where('office_service_id', $officeServiceId);
        }

        $performances = $query
            ->orderByDesc('served_count')
            ->orderBy('avg_time_minutes')
            ->limit($limit)
            ->get();

        return [
            'message' => 'Top counters performance for today.',
            'body' => CounterPerformanceDTO::fromCollection($performances),
            'total' => $performances->count(),
        ];
    }

    public function getTodaySnapshot(?int $officeServiceId = null): array
    {
        return $this->getTopPerformances($officeServiceId)['body'];
    }
}
