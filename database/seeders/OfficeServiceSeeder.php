<?php

namespace Database\Seeders;

use App\Models\OfficeService;
use Illuminate\Database\Seeder;

class OfficeServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['id' => 1, 'code' => 'A-001', 'letter' => 'A', 'name' => 'Medical Assistance', 'avg_time' => '15 mins', 'priority' => 'Normal', 'status' => 'Active', 'color' => '#1565C0', 'office_id' => 1],
            ['id' => 2, 'code' => 'B-001', 'letter' => 'B', 'name' => 'Transportation Assistance', 'avg_time' => '10 mins', 'priority' => 'Normal', 'status' => 'Active', 'color' => '#2E7D32', 'office_id' => 1],
            ['id' => 3, 'code' => 'C-001', 'letter' => 'C', 'name' => 'Burial Assistance', 'avg_time' => '12 mins', 'priority' => 'Normal', 'status' => 'Active', 'color' => '#F57C00', 'office_id' => 1],
            ['id' => 4, 'code' => 'D-001', 'letter' => 'D', 'name' => 'Cash Assistance', 'avg_time' => '10 mins', 'priority' => 'Normal', 'status' => 'Active', 'color' => '#6A1B9A', 'office_id' => 1],
            ['id' => 5, 'code' => 'E-001', 'letter' => 'E', 'name' => 'Education Assistance', 'avg_time' => '18 mins', 'priority' => 'High', 'status' => 'Active', 'color' => '#D32F2F', 'office_id' => 1],
            ['id' => 6, 'code' => 'F-001', 'letter' => 'F', 'name' => 'Food Assistance', 'avg_time' => '10 mins', 'priority' => 'Normal', 'status' => 'Active', 'color' => '#1976D2', 'office_id' => 1],
        ];

        // First truncate or clear existing to make sure we only have these 6 active services
        OfficeService::query()->forceDelete();

        foreach ($services as $service) {
            OfficeService::create($service);
        }
    }
}
