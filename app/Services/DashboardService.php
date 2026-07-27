<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Counter;
use App\Models\OfficeService;
use App\Models\Queue;
use App\Models\QueueStatus;
use Carbon\Carbon;

class DashboardService extends BaseService
{
    public function getDashboardMetrics(): array
    {
        $today = Carbon::today();

        $waitingStatus = QueueStatus::where('name', 'Waiting')->first();
        $servingStatus = QueueStatus::where('name', 'Serving')->first();
        $completedStatus = QueueStatus::where('name', 'Completed')->first();
        $cancelledStatus = QueueStatus::where('name', 'Cancelled')->first();

        $totalToday = Queue::whereDate('created_at', $today)->count();
        $servedToday = $completedStatus ? Queue::whereDate('created_at', $today)->where('queue_status_id', $completedStatus->id)->count() : 0;
        $currentlyWaiting = $waitingStatus ? Queue::where('queue_status_id', $waitingStatus->id)->count() : 0;
        $currentlyServing = $servingStatus ? Queue::where('queue_status_id', $servingStatus->id)->count() : 0;
        $currentlyCancelled = $cancelledStatus ? Queue::where('queue_status_id', $cancelledStatus->id)->count() : 0;
        $activeCountersCount = Counter::whereNotNull('user_id')->count();
        $totalCountersCount = Counter::count() ?: 1;

        $statCards = [
            [
                'label' => 'Total Transactions Today',
                'value' => (string) $totalToday,
                'comparison' => '+12% from yesterday',
                'comparisonColor' => '#2E7D32',
                'sublabel' => 'Total Queues',
                'icon' => 'mdi-swap-horizontal',
                'iconColor' => '#1A237E',
                'iconBgColor' => '#E8EAF6',
            ],
            [
                'label' => 'Served Today',
                'value' => (string) $servedToday,
                'comparison' => $totalToday ? round(($servedToday / $totalToday) * 100) . '% completion' : '0% completion',
                'comparisonColor' => '#2E7D32',
                'icon' => 'mdi-check-circle-outline',
                'iconColor' => '#2E7D32',
                'iconBgColor' => '#E8F5E9',
            ],
            [
                'label' => 'Average Wait Time',
                'value' => '8 mins',
                'comparison' => '-2 mins vs target',
                'comparisonColor' => '#2E7D32',
                'icon' => 'mdi-clock-outline',
                'iconColor' => '#E65100',
                'iconBgColor' => '#FFF3E0',
            ],
            [
                'label' => 'In Queue',
                'value' => (string) $currentlyWaiting,
                'comparison' => 'Active waiting',
                'comparisonColor' => '#1565C0',
                'icon' => 'mdi-account-clock-outline',
                'iconColor' => '#1565C0',
                'iconBgColor' => '#E3F2FD',
            ],
            [
                'label' => 'Active Counters',
                'value' => (string) $activeCountersCount,
                'comparison' => 'Of ' . $totalCountersCount . ' total counters',
                'comparisonColor' => '#1565C0',
                'icon' => 'mdi-desktop-mac-dashboard',
                'iconColor' => '#00838F',
                'iconBgColor' => '#E0F7FA',
            ],
        ];

        // 1. Recent Transactions
        $recentTransactions = Queue::with(['office_service', 'queue_status', 'counter', 'user'])
            ->orderByDesc('id')
            ->take(10)
            ->get()
            ->map(fn($q) => [
                'id' => $q->id,
                'queueNumber' => $q->queue_no,
                'service' => $q->office_service?->name ?? 'General Service',
                'clientName' => $q->client_name ?? 'Walk-in Client',
                'counter' => $q->counter?->name ?? '—',
                'assignedOfficer' => $q->user?->fullname ?? 'Officer',
                'status' => $q->queue_status?->name ?? 'Waiting',
                'timeCreated' => $q->time_start ? substr($q->time_start, 11, 5) : '—',
                'timeServed' => $q->time_end ? substr($q->time_end, 11, 5) : '—',
                'date' => $q->created_at ? substr((string) $q->created_at, 0, 10) : date('Y-m-d'),
            ]);

        // 2. Transactions Status Distribution
        $allQueuesCount = Queue::count() ?: 1;
        $statuses = QueueStatus::withCount('queues')->get();
        $transactionsStatus = $statuses->map(fn($st) => [
            'status' => $st->name,
            'count' => $st->queues_count,
            'percentage' => round(($st->queues_count / $allQueuesCount) * 100),
        ]);

        // 3. Transactions by Service
        $serviceTransactions = OfficeService::withCount('queues')->get()->map(fn($srv) => [
            'name' => $srv->name,
            'letter' => $srv->letter,
            'color' => $srv->color ?? '#1565C0',
            'count' => $srv->queues_count,
        ]);

        // 4. Past 7 Days Overview Trend
        $past7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);
            $count = Queue::whereDate('created_at', $date)->count();
            return [
                'day' => $date->format('D'),
                'date' => $date->format('Y-m-d'),
                'count' => $count,
            ];
        });

        // 5. Top Counters Leaderboard
        $topCounters = Counter::with(['user', 'office_service'])
            ->withCount(['queues as completed_count' => function ($q) {
                $q->whereHas('queue_status', fn($qs) => $qs->where('name', 'Completed'));
            }])
            ->orderByDesc('completed_count')
            ->take(5)
            ->get()
            ->map(fn($c) => [
                'counter' => $c->name,
                'officer' => $c->user?->fullname ?? 'Service Officer',
                'service' => $c->office_service?->name ?? 'General Service',
                'completedCount' => $c->completed_count,
            ]);

        // 6. Live Activity Feed
        $liveActivityFeed = Queue::with(['queue_status', 'counter'])
            ->whereNotNull('time_start')
            ->orderByDesc('updated_at')
            ->take(6)
            ->get()
            ->map(fn($q) => [
                'id' => $q->id,
                'ticketNumber' => $q->queue_no,
                'action' => $q->queue_status?->name === 'Serving'
                    ? 'Called to ' . ($q->counter?->name ?? 'Counter')
                    : 'Status set to ' . ($q->queue_status?->name ?? 'Waiting'),
                'time' => $q->updated_at ? $q->updated_at->diffForHumans() : 'Just now',
                'status' => $q->queue_status?->name ?? 'Waiting',
            ]);

        // 7. Live Queue Snapshot
        $liveQueueSnapshot = Queue::with(['office_service', 'counter', 'queue_status'])
            ->whereIn('queue_status_id', [$waitingStatus?->id ?? 1, $servingStatus?->id ?? 2])
            ->take(10)
            ->get()
            ->map(fn($q) => [
                'ticketNumber' => $q->queue_no,
                'service' => $q->office_service?->name ?? 'General Service',
                'counter' => $q->counter?->name ?? 'Waiting',
                'status' => $q->queue_status?->name ?? 'Waiting',
            ]);

        $announcements = Announcement::where('status', 'Published')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        $activeCounters = Counter::with(['user', 'office_service'])->get();

        return [
            'message' => 'Dashboard metrics retrieved.',
            'body' => [
                'statistic_cards' => $statCards,
                'recent_transactions' => $recentTransactions,
                'transactions_status' => $transactionsStatus,
                'service_transactions' => $serviceTransactions,
                'transactions_overview' => $past7Days,
                'top_counters' => $topCounters,
                'live_activity_feed' => $liveActivityFeed,
                'live_queue_snapshot' => $liveQueueSnapshot,
                'announcements' => $announcements,
                'active_counters' => $activeCounters,
                'counts' => [
                    'total_today' => $totalToday,
                    'served_today' => $servedToday,
                    'waiting' => $currentlyWaiting,
                    'serving' => $currentlyServing,
                    'active_counters' => $activeCountersCount,
                ],
            ],
        ];
    }
}