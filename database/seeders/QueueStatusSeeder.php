<?php

namespace Database\Seeders;

use App\Models\QueueStatus;
use Illuminate\Database\Seeder;

class QueueStatusSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Remap all queue records pointing to ID >= 4 to ID 1 temporarily
        \Illuminate\Support\Facades\DB::table('queues')
            ->whereIn('queue_status_id', [4, 5, 6])
            ->update(['queue_status_id' => 1]);

        // 2. Delete all records from queue_statuses with ID >= 4 to avoid name unique constraint conflicts
        \Illuminate\Support\Facades\DB::table('queue_statuses')
            ->where('id', '>=', 4)
            ->delete();

        // 3. Insert or update the 4 canonical statuses
        $statuses = [
            ['id' => 1, 'name' => 'Waiting'],
            ['id' => 2, 'name' => 'Serving'],
            ['id' => 3, 'name' => 'Completed'],
            ['id' => 4, 'name' => 'Cancelled'],
        ];

        foreach ($statuses as $status) {
            QueueStatus::updateOrCreate(['id' => $status['id']], $status);
        }
    }
}

