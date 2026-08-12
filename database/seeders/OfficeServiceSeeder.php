<?php

namespace Database\Seeders;

use App\Models\OfficeService;
use Illuminate\Database\Seeder;

class OfficeServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['id' => 1, 'name' => 'Medical Assistance', 'code' => 'MA', 'office_id' => 1, 'office_service_category_id' => 1],
            ['id' => 2, 'name' => 'Transportation Assistance', 'code' => 'TA', 'office_id' => 1, 'office_service_category_id' => 2],
            ['id' => 3, 'name' => 'Burial Assistance', 'code' => 'BA', 'office_id' => 1, 'office_service_category_id' => 3],
            ['id' => 4, 'name' => 'Cash Assistance', 'code' => 'CA', 'office_id' => 1, 'office_service_category_id' => 4],
            ['id' => 5, 'name' => 'Education Assistance', 'code' => 'EA', 'office_id' => 1, 'office_service_category_id' => 5],
            ['id' => 6, 'name' => 'Food Assistance', 'code' => 'FA', 'office_id' => 1, 'office_service_category_id' => 6],
        ];

        foreach ($services as $service) {
            OfficeService::updateOrCreate(['id' => $service['id']], $service);
        }
    }
}

