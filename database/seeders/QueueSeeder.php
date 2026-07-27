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
            [
                'queue_no' => 'X7K9P',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Juan Dela Cruz',
                'client_phone' => '09171234567',
                'purpose' => 'Medical Assistance Request',
                'priority' => 'Normal',
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 1,
                'counter_id' => 1,
                'user_id' => 2,
                'time_start' => $today->copy()->setHour(8)->setMinute(15),
                'time_end' => $today->copy()->setHour(8)->setMinute(30),
                'created_at' => $today->copy()->setHour(8)->setMinute(10),
            ],
            [
                'queue_no' => '8M2A5',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Pedro Reyes',
                'client_phone' => '09187654321',
                'purpose' => 'Transportation Assistance Request',
                'priority' => 'Normal',
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 2,
                'counter_id' => 2,
                'user_id' => 3,
                'time_start' => $today->copy()->setHour(8)->setMinute(35),
                'time_end' => $today->copy()->setHour(8)->setMinute(50),
                'created_at' => $today->copy()->setHour(8)->setMinute(20),
            ],
            [
                'queue_no' => '3P9L2',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Ana Garcia',
                'client_phone' => '09201112233',
                'purpose' => 'Burial Assistance Request',
                'priority' => 'Normal',
                'queue_status_id' => $statusMap['Serving'] ?? 2,
                'office_service_id' => 3,
                'counter_id' => 3,
                'user_id' => 4,
                'time_start' => $today->copy()->setHour(9)->setMinute(00),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(8)->setMinute(45),
            ],
            [
                'queue_no' => '9F2M1',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Maria Santos',
                'client_phone' => '09193332211',
                'purpose' => 'Cash Assistance',
                'priority' => 'Normal',
                'queue_status_id' => $statusMap['Serving'] ?? 2,
                'office_service_id' => 4,
                'counter_id' => 4,
                'user_id' => 7,
                'time_start' => $today->copy()->setHour(9)->setMinute(10),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(8)->setMinute(50),
            ],
            [
                'queue_no' => 'K4T8W',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'David Lee',
                'client_phone' => '09176667788',
                'purpose' => 'Education Assistance Request',
                'priority' => 'High',
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 5,
                'counter_id' => 5,
                'user_id' => 8,
                'time_start' => $today->copy()->setHour(7)->setMinute(45),
                'time_end' => $today->copy()->setHour(8)->setMinute(15),
                'created_at' => $today->copy()->setHour(7)->setMinute(30),
            ],
            [
                'queue_no' => 'R6N1V',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Jose Ramirez',
                'client_phone' => '09187778899',
                'purpose' => 'Food Assistance',
                'priority' => 'Normal',
                'queue_status_id' => $statusMap['Cancelled'] ?? 4,
                'office_service_id' => 6,
                'counter_id' => 6,
                'user_id' => 5,
                'time_start' => $today->copy()->setHour(8)->setMinute(00),
                'time_end' => $today->copy()->setHour(8)->setMinute(10),
                'created_at' => $today->copy()->setHour(7)->setMinute(40),
            ],
            [
                'queue_no' => 'B5H7Y',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Ramon Cruz',
                'client_phone' => '09154445566',
                'purpose' => 'Medical Assistance Request',
                'priority' => 'Normal',
                'queue_status_id' => $statusMap['Waiting'] ?? 1,
                'office_service_id' => 1,
                'counter_id' => null,
                'user_id' => null,
                'time_start' => $today->copy()->setHour(9)->setMinute(15),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(9)->setMinute(15),
            ],
            [
                'queue_no' => '7Q3Z8',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Sophie Lim',
                'client_phone' => '09165556677',
                'purpose' => 'Transportation Assistance Request',
                'priority' => 'Normal',
                'queue_status_id' => $statusMap['Waiting'] ?? 1,
                'office_service_id' => 2,
                'counter_id' => null,
                'user_id' => null,
                'time_start' => $today->copy()->setHour(9)->setMinute(20),
                'time_end' => null,
                'created_at' => $today->copy()->setHour(9)->setMinute(20),
            ],

            // YESTERDAY TRANSACTIONS
            [
                'queue_no' => 'L2V4K',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Elena Torres',
                'client_phone' => '09191112244',
                'purpose' => 'Medical Assistance Request',
                'priority' => 'Normal',
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 1,
                'counter_id' => 1,
                'user_id' => 2,
                'time_start' => $yesterday->copy()->setHour(9)->setMinute(30),
                'time_end' => $yesterday->copy()->setHour(9)->setMinute(48),
                'created_at' => $yesterday->copy()->setHour(9)->setMinute(00),
            ],
            [
                'queue_no' => '4J8W9',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Carlos Mendoza',
                'client_phone' => '09202223355',
                'purpose' => 'Education Assistance',
                'priority' => 'Normal',
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 5,
                'counter_id' => 5,
                'user_id' => 8,
                'time_start' => $yesterday->copy()->setHour(10)->setMinute(15),
                'time_end' => $yesterday->copy()->setHour(10)->setMinute(35),
                'created_at' => $yesterday->copy()->setHour(10)->setMinute(00),
            ],
            [
                'queue_no' => 'T9P3M',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Grace Bautista',
                'client_phone' => '09213334466',
                'purpose' => 'Cash Assistance',
                'priority' => 'High',
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
                'queue_no' => 'G5R7X',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Antonio Luna',
                'client_phone' => '09246667799',
                'purpose' => 'Medical Assistance Request',
                'priority' => 'Normal',
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 1,
                'counter_id' => 1,
                'user_id' => 2,
                'time_start' => $twoDaysAgo->copy()->setHour(9)->setMinute(00),
                'time_end' => $twoDaysAgo->copy()->setHour(9)->setMinute(18),
                'created_at' => $twoDaysAgo->copy()->setHour(8)->setMinute(40),
            ],
            [
                'queue_no' => 'W1Y6B',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Beatriz Santos',
                'client_phone' => '09257778800',
                'purpose' => 'Burial Assistance Request',
                'priority' => 'Normal',
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
                'queue_no' => 'N8K2C',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Diego Silang',
                'client_phone' => '09279990022',
                'purpose' => 'Education Assistance',
                'priority' => 'Normal',
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 5,
                'counter_id' => 5,
                'user_id' => 8,
                'time_start' => $threeDaysAgo->copy()->setHour(9)->setMinute(15),
                'time_end' => $threeDaysAgo->copy()->setHour(9)->setMinute(35),
                'created_at' => $threeDaysAgo->copy()->setHour(8)->setMinute(55),
            ],

            // 5 DAYS AGO
            [
                'queue_no' => 'F4D9T',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Felix Manalo',
                'client_phone' => '09291112244',
                'purpose' => 'Medical Assistance Request',
                'priority' => 'Normal',
                'queue_status_id' => $statusMap['Completed'] ?? 3,
                'office_service_id' => 1,
                'counter_id' => 1,
                'user_id' => 2,
                'time_start' => $fiveDaysAgo->copy()->setHour(8)->setMinute(45),
                'time_end' => $fiveDaysAgo->copy()->setHour(9)->setMinute(05),
                'created_at' => $fiveDaysAgo->copy()->setHour(8)->setMinute(30),
            ],
            [
                'queue_no' => 'V7S3A',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Gloria Arroyo',
                'client_phone' => '09302223355',
                'purpose' => 'Cash Assistance',
                'priority' => 'Normal',
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
                'queue_no' => 'H2L8E',
                'uuid' => (string) Str::uuid(),
                'client_name' => 'Hector Laurel',
                'client_phone' => '09313334466',
                'purpose' => 'Burial Assistance Request',
                'priority' => 'Normal',
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
