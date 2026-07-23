<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->unsignedInteger('estimated_wait_minutes')->nullable()->after('time_end');
            $table->dateTime('estimated_time_return')->nullable()->after('estimated_wait_minutes');
        });

        Schema::create('counter_performances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('counter_id');
            $table->unsignedBigInteger('office_service_id')->nullable();
            $table->date('performance_date');
            $table->unsignedInteger('served_count')->default(0);
            $table->decimal('total_service_minutes', 8, 2)->default(0);
            $table->decimal('avg_time_minutes', 8, 2)->nullable();
            $table->decimal('efficiency_percent', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['counter_id', 'performance_date']);
            $table->foreign('counter_id')->references('id')->on('counters')->onDelete('cascade');
            $table->foreign('office_service_id')->references('id')->on('office_services')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counter_performances');

        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn(['estimated_wait_minutes', 'estimated_time_return']);
        });
    }
};
