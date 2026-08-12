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
        Schema::table('queues', function (Blueprint $table) {
            if (!Schema::hasColumn('queues', 'remarks')) {
                $table->text('remarks')->nullable()->after('time_end');
            }
            if (!Schema::hasColumn('queues', 'transferred_from_counter_id')) {
                $table->unsignedBigInteger('transferred_from_counter_id')->nullable()->after('counter_id');
                $table->foreign('transferred_from_counter_id')->references('id')->on('counters')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            if (Schema::hasColumn('queues', 'transferred_from_counter_id')) {
                $table->dropForeign(['transferred_from_counter_id']);
                $table->dropColumn('transferred_from_counter_id');
            }
            if (Schema::hasColumn('queues', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};
