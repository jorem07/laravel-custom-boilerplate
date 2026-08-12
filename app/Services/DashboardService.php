<?php

namespace App\Services;

use App\Models\Queue;
use Carbon\Carbon;

class DashboardService
{
    protected CounterService $counterService;
    protected CounterPerformanceService $counterPerformanceService;

    public function __construct(
        CounterService $counterService,
        CounterPerformanceService $counterPerformanceService,
    ) {
        $this->counterService = $counterService;
        $this->counterPerformanceService = $counterPerformanceService;
    }

    public function getDashboardMetrics(array $payload = []): array
    {
        $targetDate = !empty($payload['date']) ? Carbon::parse($payload['date']) : Carbon::today();
        $dateStr = $targetDate->format('Y-m-d');

        $totalQueues = Queue::whereDate('time_start', $targetDate)->count();
        $waitingCount = Queue::whereDate('time_start', $targetDate)
            ->whereHas('queue_status', fn($q) => $q->where('name', 'Waiting'))
            ->count();
        $servingCount = Queue::whereDate('time_start', $targetDate)
            ->whereHas('queue_status', fn($q) => $q->where('name', 'Serving'))
            ->count();
        $completedCount = Queue::whereDate('time_start', $targetDate)
            ->whereHas('queue_status', fn($q) => $q->where('name', 'Completed'))
            ->count();

        $snapshot = $this->counterService->getSnapshot();
        $performances = $this->counterPerformanceService->getTodaySnapshot();

        return [
            'total_queues' => $totalQueues,
            'waiting_count' => $waitingCount,
            'serving_count' => $servingCount,
            'completed_count' => $completedCount,
            'active_counters' => $snapshot['counters'],
            'top_counters' => $performances,
            'date' => $dateStr,
        ];
    }

    public function getTotalData(): array
    {
        return $this->getDashboardMetrics();
    }
}