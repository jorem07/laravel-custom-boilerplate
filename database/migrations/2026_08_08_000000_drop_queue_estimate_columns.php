<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            if (Schema::hasColumn('queues', 'estimated_wait_minutes')) {
                $table->dropColumn('estimated_wait_minutes');
            }
            if (Schema::hasColumn('queues', 'estimated_time_return')) {
                $table->dropColumn('estimated_time_return');
            }
        });
    }

    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->unsignedInteger('estimated_wait_minutes')->nullable()->after('time_end');
            $table->dateTime('estimated_time_return')->nullable()->after('estimated_wait_minutes');
        });
    }
};
