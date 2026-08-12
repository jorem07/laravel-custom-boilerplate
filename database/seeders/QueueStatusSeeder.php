<?php

namespace Database\Seeders;

use App\Models\QueueStatus;
use Illuminate\Database\Seeder;

class QueueStatusSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Insert or update canonical statuses first
        $statuses = [
            ['id' => 1, 'name' => 'Waiting'],
            ['id' => 2, 'name' => 'Serving'],
            ['id' => 3, 'name' => 'Completed'],
            ['id' => 4, 'name' => 'Cancelled'],
            ['id' => 6, 'name' => 'Skipped'],
        ];

        foreach ($statuses as $status) {
            QueueStatus::updateOrCreate(['id' => $status['id']], $status);
        }

        // 2. Remap any legacy queue records with ID 5 or ID >= 7 to 1
        if (\Illuminate\Support\Facades\Schema::hasTable('queues')) {
            \Illuminate\Support\Facades\DB::table('queues')
                ->where('queue_status_id', 5)
                ->orWhere('queue_status_id', '>=', 7)
                ->update(['queue_status_id' => 1]);
        }

        // 3. Delete obsolete records (ID 5, ID >= 7)
        \Illuminate\Support\Facades\DB::table('queue_statuses')
            ->where('id', 5)
            ->orWhere('id', '>=', 7)
            ->delete();
    }
}

