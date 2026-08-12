<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $tables = [
        'offices' => 'offices_id_seq',
        'office_service_categories' => 'office_service_categories_id_seq',
        'office_services' => 'office_services_id_seq',
        'office_service_requirements' => 'office_service_requirements_id_seq',
        'counters' => 'counters_id_seq',
        'users' => 'users_id_seq',
        'roles' => 'roles_id_seq',
        'queues' => 'queues_id_seq',
        'queue_statuses' => 'queue_statuses_id_seq',
    ];

    foreach ($tables as $table => $seq) {
        $maxId = DB::table($table)->max('id') ?? 0;
        $nextVal = max($maxId, 1);
        DB::statement("SELECT setval('{$seq}', {$nextVal})");
        echo "Synced {$table} sequence ({$seq}) to max ID: {$nextVal}\n";
    }

    echo "ALL SEQUENCES SYNCED CLEANLY!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
