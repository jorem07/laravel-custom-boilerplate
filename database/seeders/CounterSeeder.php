<?php

namespace Database\Seeders;

use App\Models\Counter;
use Illuminate\Database\Seeder;

class CounterSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            "Ground Floor - Left Wing",
            "Ground Floor - Left Wing",
            "Ground Floor - Center",
            "Ground Floor - Center",
            "Ground Floor - Right Wing",
            "Second Floor - Left Wing",
            "Second Floor - Left Wing",
            "Second Floor - Right Wing",
            "Third Floor - Center",
            "Third Floor - Right Wing",
            "Third Floor - Center",
            "Third Floor - Right Wing",
        ];

        for ($i = 1; $i <= 12; $i++) {
            $isAssigned = ($i <= 10);
            Counter::updateOrCreate(['id' => $i], [
                'name' => "Counter {$i}",
                'location' => $locations[$i - 1],
                'queue_display_status' => $isAssigned ? 'Connected' : 'Disconnected',
                'user_id' => $isAssigned ? ($i === 1 ? 2 : ($i === 2 ? 3 : ($i === 3 ? 4 : ($i === 4 ? 7 : ($i === 5 ? 8 : ($i === 6 ? 5 : ($i === 7 ? 6 : ($i === 8 ? 9 : ($i === 9 ? 10 : 11))))))))) : null,
                'office_service_id' => ($i - 1) % 6 + 1,
            ]);
        }
    }
}
