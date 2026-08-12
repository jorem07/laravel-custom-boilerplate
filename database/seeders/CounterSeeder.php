<?php

namespace Database\Seeders;

use App\Models\Counter;
use Illuminate\Database\Seeder;

class CounterSeeder extends Seeder
{
    public function run(): void
    {
        $counters = [
            ['id' => 1, 'name' => 'Counter 1', 'user_id' => null, 'office_service_id' => 1, 'service_ids' => [1, 2]],
            ['id' => 2, 'name' => 'Counter 2', 'user_id' => null, 'office_service_id' => 2, 'service_ids' => [2, 3]],
            ['id' => 3, 'name' => 'Counter 3', 'user_id' => null, 'office_service_id' => 3, 'service_ids' => [3]],
            ['id' => 4, 'name' => 'Counter 4', 'user_id' => null, 'office_service_id' => 4, 'service_ids' => [4]],
            ['id' => 5, 'name' => 'Counter 5', 'user_id' => null, 'office_service_id' => 5, 'service_ids' => [5]],
            ['id' => 6, 'name' => 'Counter 6', 'user_id' => null, 'office_service_id' => 6, 'service_ids' => [6]],
        ];

        foreach ($counters as $counter) {
            Counter::updateOrCreate(['id' => $counter['id']], $counter);
        }
    }
}


