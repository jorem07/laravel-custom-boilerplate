<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('counters', function (Blueprint $table) {
            if (!Schema::hasColumn('counters', 'service_ids')) {
                $table->json('service_ids')->nullable()->after('office_service_id');
            }
        });

        Schema::table('queues', function (Blueprint $table) {
            $table->index(['office_service_id', 'queue_status_id'], 'idx_queues_service_status');
            $table->index(['counter_id', 'queue_status_id'], 'idx_queues_counter_status');
            $table->index(['created_at'], 'idx_queues_created_at');
        });

        Schema::table('counter_user_logs', function (Blueprint $table) {
            $table->index(['counter_id', 'log_in'], 'idx_counter_logs_counter_login');
            $table->index(['user_id', 'log_in'], 'idx_counter_logs_user_login');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counters', function (Blueprint $table) {
            if (Schema::hasColumn('counters', 'service_ids')) {
                $table->dropColumn('service_ids');
            }
        });

        Schema::table('queues', function (Blueprint $table) {
            $table->dropIndex('idx_queues_service_status');
            $table->dropIndex('idx_queues_counter_status');
            $table->dropIndex('idx_queues_created_at');
        });

        Schema::table('counter_user_logs', function (Blueprint $table) {
            $table->dropIndex('idx_counter_logs_counter_login');
            $table->dropIndex('idx_counter_logs_user_login');
        });
    }
};
