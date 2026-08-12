<?php

namespace Database\Seeders;

use App\Models\Queue;
use App\Models\QueueStatus;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QueueSeeder extends Seeder
{
    public function run(): void
    {
        $statusMap = QueueStatus::pluck('id', 'name')->toArray();

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $twoDaysAgo = Carbon::today()->subDays(2);
        $threeDaysAgo = Carbon::today()->subDays(3);
        $fiveDaysAgo = Carbon::today()->subDays(5);
        $sevenDaysAgo = Carbon::today()->subDays(7);

        // Truncate existing queues for clean fresh dataset
        Queue::query()->forceDelete();

        $queues = [
            // TODAY TRANSACTIONS
            // Medical Assistance (Service 1, Counter 1)
            [
                'queue_no' => 'MA-001',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Serving'] ?? 2,
                'office_service_id' => 1,
                'counter_id' => 1,
                'user_id' => 2,
                'time_start' => $today->copy()->setHour(9)->setMinute(00),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(8)->setMinute(45),
            ],
            [
                'queue_no' => 'MA-002',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Waiting'] ?? 1,
                'office_service_id' => 1,
                'counter_id' => null,
                'user_id' => null,
                'time_start' => $today->copy()->setHour(9)->setMinute(15),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(9)->setMinute(15),
            ],
            [
                'queue_no' => 'MA-003',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Waiting'] ?? 1,
                'office_service_id' => 1,
                'counter_id' => null,
                'user_id' => null,
                'time_start' => $today->copy()->setHour(9)->setMinute(30),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(9)->setMinute(30),
            ],
            [
                'queue_no' => 'MA-004',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Pending'] ?? 5,
                'office_service_id' => 1,
                'counter_id' => 1,
                'user_id' => 2,
                'time_start' => $today->copy()->setHour(8)->setMinute(30),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(8)->setMinute(20),
            ],

            // Transportation Assistance (Service 2, Counter 2)
            [
                'queue_no' => 'TA-001',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Serving'] ?? 2,
                'office_service_id' => 2,
                'counter_id' => 2,
                'user_id' => 3,
                'time_start' => $today->copy()->setHour(9)->setMinute(05),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(8)->setMinute(50),
            ],
            [
                'queue_no' => 'TA-002',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Waiting'] ?? 1,
                'office_service_id' => 2,
                'counter_id' => null,
                'user_id' => null,
                'time_start' => $today->copy()->setHour(9)->setMinute(20),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(9)->setMinute(20),
            ],

            // Burial Assistance (Service 3, Counter 3)
            [
                'queue_no' => 'BA-001',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Serving'] ?? 2,
                'office_service_id' => 3,
                'counter_id' => 3,
                'user_id' => 4,
                'time_start' => $today->copy()->setHour(9)->setMinute(00),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(8)->setMinute(45),
            ],
            [
                'queue_no' => 'BA-002',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Waiting'] ?? 1,
                'office_service_id' => 3,
                'counter_id' => null,
                'user_id' => null,
                'time_start' => $today->copy()->setHour(9)->setMinute(25),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(9)->setMinute(25),
            ],

            // Cash Assistance (Service 4, Counter 4)
            [
                'queue_no' => 'CA-001',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Serving'] ?? 2,
                'office_service_id' => 4,
                'counter_id' => 4,
                'user_id' => 7,
                'time_start' => $today->copy()->setHour(9)->setMinute(10),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(8)->setMinute(50),
            ],
            [
                'queue_no' => 'CA-002',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Waiting'] ?? 1,
                'office_service_id' => 4,
                'counter_id' => null,
                'user_id' => null,
                'time_start' => $today->copy()->setHour(9)->setMinute(35),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(9)->setMinute(35),
            ],

            // Education Assistance (Service 5, Counter 5)
            [
                'queue_no' => 'EA-001',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Serving'] ?? 2,
                'office_service_id' => 5,
                'counter_id' => 5,
                'user_id' => 6,
                'time_start' => $today->copy()->setHour(9)->setMinute(00),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(8)->setMinute(40),
            ],
            [
                'queue_no' => 'EA-002',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Waiting'] ?? 1,
                'office_service_id' => 5,
                'counter_id' => null,
                'user_id' => null,
                'time_start' => $today->copy()->setHour(9)->setMinute(40),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(9)->setMinute(40),
            ],

            // Food Assistance (Service 6, Counter 6)
            [
                'queue_no' => 'FA-001',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Serving'] ?? 2,
                'office_service_id' => 6,
                'counter_id' => 6,
                'user_id' => 5,
                'time_start' => $today->copy()->setHour(9)->setMinute(10),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(8)->setMinute(55),
            ],
            [
                'queue_no' => 'FA-002',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Waiting'] ?? 1,
                'office_service_id' => 6,
                'counter_id' => null,
                'user_id' => null,
                'time_start' => $today->copy()->setHour(9)->setMinute(45),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(9)->setMinute(45),
            ],

            // YESTERDAY TRANSACTIONS
            [
                'queue_no' => 'MA-001',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 1,
                'counter_id' => 1,
                'user_id' => 2,
                'time_start' => $yesterday->copy()->setHour(9)->setMinute(30),
                'time_end' => $yesterday->copy()->setHour(9)->setMinute(48),
                'created_at' => $yesterday->copy()->setHour(9)->setMinute(00),
            ],
            [
                'queue_no' => 'EA-001',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 5,
                'counter_id' => 5,
                'user_id' => 6,
                'time_start' => $yesterday->copy()->setHour(10)->setMinute(15),
                'time_end' => $yesterday->copy()->setHour(10)->setMinute(35),
                'created_at' => $yesterday->copy()->setHour(10)->setMinute(00),
            ],
            [
                'queue_no' => 'CA-001',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 4,
                'counter_id' => 4,
                'user_id' => 7,
                'time_start' => $yesterday->copy()->setHour(11)->setMinute(00),
                'time_end' => $yesterday->copy()->setHour(11)->setMinute(22),
                'created_at' => $yesterday->copy()->setHour(10)->setMinute(45),
            ],

            // 2 DAYS AGO
            [
                'queue_no' => 'MA-001',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 1,
                'counter_id' => 1,
                'user_id' => 2,
                'time_start' => $twoDaysAgo->copy()->setHour(9)->setMinute(00),
                'time_end' => $twoDaysAgo->copy()->setHour(9)->setMinute(18),
                'created_at' => $twoDaysAgo->copy()->setHour(8)->setMinute(40),
            ],
            [
                'queue_no' => 'BA-001',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 3,
                'counter_id' => 3,
                'user_id' => 4,
                'time_start' => $twoDaysAgo->copy()->setHour(10)->setMinute(00),
                'time_end' => $twoDaysAgo->copy()->setHour(10)->setMinute(20),
                'created_at' => $twoDaysAgo->copy()->setHour(9)->setMinute(45),
            ],

            // 3 DAYS AGO
            [
                'queue_no' => 'EA-001',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 5,
                'counter_id' => 5,
                'user_id' => 6,
                'time_start' => $threeDaysAgo->copy()->setHour(9)->setMinute(15),
                'time_end' => $threeDaysAgo->copy()->setHour(9)->setMinute(35),
                'created_at' => $threeDaysAgo->copy()->setHour(8)->setMinute(55),
            ],

            // 5 DAYS AGO
            [
                'queue_no' => 'MA-001',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 1,
                'counter_id' => 1,
                'user_id' => 2,
                'time_start' => $fiveDaysAgo->copy()->setHour(8)->setMinute(45),
                'time_end' => $fiveDaysAgo->copy()->setHour(9)->setMinute(05),
                'created_at' => $fiveDaysAgo->copy()->setHour(8)->setMinute(30),
            ],
            [
                'queue_no' => 'CA-001',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 4,
                'counter_id' => 4,
                'user_id' => 7,
                'time_start' => $fiveDaysAgo->copy()->setHour(14)->setMinute(00),
                'time_end' => $fiveDaysAgo->copy()->setHour(14)->setMinute(20),
                'created_at' => $fiveDaysAgo->copy()->setHour(13)->setMinute(45),
            ],

            // 7 DAYS AGO
            [
                'queue_no' => 'BA-001',
                'uuid' => (string) Str::uuid(),
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 3,
                'counter_id' => 3,
                'user_id' => 4,
                'time_start' => $sevenDaysAgo->copy()->setHour(9)->setMinute(20),
                'time_end' => $sevenDaysAgo->copy()->setHour(9)->setMinute(40),
                'created_at' => $sevenDaysAgo->copy()->setHour(9)->setMinute(05),
            ],
        ];

        foreach ($queues as $q) {
            Queue::create($q);
        }
    }
}
