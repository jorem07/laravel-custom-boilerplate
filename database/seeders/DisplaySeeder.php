<?php

namespace Database\Seeders;

use App\Models\Display;
use Illuminate\Database\Seeder;

class DisplaySeeder extends Seeder
{
    public function run(): void
    {
        $displays = [
            [
                'id' => 1,
                'name' => 'Main Lobby Display 1',
                'slug' => 'main-lobby-1',
                'location' => 'Ground Floor - Main Lobby',
                'status' => 'Active',
                'layout_type' => 'standard',
                'connection_status' => 'Connected',
                'last_heartbeat_at' => now(),
                'office_id' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Second Floor Display 2',
                'slug' => 'second-floor-2',
                'location' => 'Second Floor - Waiting Area',
                'status' => 'Active',
                'layout_type' => 'main_lobby',
                'connection_status' => 'Connected',
                'last_heartbeat_at' => now(),
                'office_id' => 1,
            ],
            [
                'id' => 3,
                'name' => 'Counters Focus Display 3',
                'slug' => 'counters-focus-3',
                'location' => 'Counter Hallway',
                'status' => 'Active',
                'layout_type' => 'counter_grid',
                'connection_status' => 'Connected',
                'last_heartbeat_at' => now(),
                'office_id' => 1,
            ],
        ];

        foreach ($displays as $d) {
            Display::updateOrCreate(['id' => $d['id']], $d);
        }
    }
}
