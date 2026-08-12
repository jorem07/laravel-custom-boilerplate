<?php

namespace Database\Seeders;

use App\Models\OfficeServiceCategory;
use Illuminate\Database\Seeder;

class OfficeServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['id' => 1, 'type' => 'A', 'office_id' => 1],
            ['id' => 2, 'type' => 'B', 'office_id' => 1],
            ['id' => 3, 'type' => 'C', 'office_id' => 1],
            ['id' => 4, 'type' => 'D', 'office_id' => 1],
            ['id' => 5, 'type' => 'E', 'office_id' => 1],
            ['id' => 6, 'type' => 'F', 'office_id' => 1],
        ];

        foreach ($categories as $category) {
            OfficeServiceCategory::updateOrCreate(['id' => $category['id']], $category);
        }
    }
}
