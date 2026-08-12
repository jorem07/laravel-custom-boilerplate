<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            OfficeServiceCategorySeeder::class,
            OfficeServiceSeeder::class,
            ServiceRequirementSeeder::class,
            QueueStatusSeeder::class,
            UserSeeder::class,
            CounterSeeder::class,
            DisplaySeeder::class,
            AnnouncementSeeder::class,
            QueueSeeder::class,
        ]);

        // Reset PostgreSQL sequences after seeding with explicit IDs
        // Without this, new inserts fail with "duplicate key" errors
        $this->resetPostgresSequences();
    }

    /**
     * Reset auto-increment sequences for all tables that use explicit IDs in seeders.
     */
    private function resetPostgresSequences(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $tables = DB::select("
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = 'public'
              AND table_type = 'BASE TABLE'
              AND table_name NOT LIKE '%migrations%'
        ");

        foreach ($tables as $table) {
            $tableName = $table->table_name;
            if (!\Illuminate\Support\Facades\Schema::hasColumn($tableName, 'id')) {
                continue;
            }
            $seq = DB::selectOne("SELECT pg_get_serial_sequence('{$tableName}', 'id') as seq");

            if ($seq && $seq->seq) {
                $maxId = DB::table($tableName)->max('id') ?? 0;
                DB::statement("SELECT setval('{$seq->seq}', " . ($maxId + 1) . ", false)");
            }
        }
    }
}


