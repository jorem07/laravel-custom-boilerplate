<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            OfficeSeeder::class,
            OfficeServiceSeeder::class,
            ServiceRequirementSeeder::class,
            UserSeeder::class,
            CounterSeeder::class,
            QueueStatusSeeder::class,
            QueueSeeder::class,
            AnnouncementSeeder::class,
            DisplaySeeder::class,
        ]);
        $this->resetPostgresSequences();
    }

    /**
     * Reset PostgreSQL sequences for tables that were seeded with explicit IDs.
     */
    protected function resetPostgresSequences(): void
    {
        $tables = [
            'users',
            'offices',
            'office_services',
            'service_requirements',
            'counters',
            'queue_statuses',
            'queues',
            'announcements',
            'displays',
            'roles'
        ];

        foreach ($tables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                try {
                    $max = \Illuminate\Support\Facades\DB::table($table)->max('id') ?? 0;
                    \Illuminate\Support\Facades\DB::statement("SELECT setval('{$table}_id_seq', " . max($max, 1) . ")");
                } catch (\Exception $e) {
                    // Ignore if sequence doesn't exist or table isn't using sequences
                }
            }
        }
    }
}
