<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        Office::firstOrCreate([
            'id' => 1,
        ], [
            'name' => "People's Assistance Office (DSWD Camiguin)",
        ]);
    }
}
