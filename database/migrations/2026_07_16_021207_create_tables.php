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
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('office_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->string('letter', 5)->default('A');
            $table->string('avg_time')->nullable();
            $table->string('priority')->default('Normal');
            $table->string('status')->default('Active');
            $table->string('color')->nullable();
            $table->unsignedBigInteger('office_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('office_id')->references('id')->on('offices')->onDelete('cascade');

            $table->index('code');
            $table->index('letter');
            $table->index('status');
        });

        Schema::create('service_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('office_service_id');
            $table->text('description');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('office_service_id')->references('id')->on('office_services')->onDelete('cascade');
        });

        Schema::create('counters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('queue_display_status')->default('Disconnected');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('office_service_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('office_service_id')->references('id')->on('office_services')->onDelete('set null');
        });

        Schema::create('counter_user_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('counter_id')->nullable();
            $table->dateTime('log_in')->nullable();
            $table->dateTime('log_out')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('counter_id')->references('id')->on('counters')->onDelete('cascade');

            $table->index(['counter_id', 'log_out']);
            $table->index(['user_id', 'log_out']);
            $table->index('log_in');
        });

        Schema::create('queue_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->string('queue_no');
            $table->uuid('uuid')->unique();
            $table->string('token')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_phone')->nullable();
            $table->text('purpose')->nullable();
            $table->string('priority')->default('Normal');
            $table->string('priority_type')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->unsignedBigInteger('queue_status_id');
            $table->unsignedBigInteger('office_service_id')->nullable();
            $table->unsignedBigInteger('counter_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->dateTime('time_start');
            $table->dateTime('time_end')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('queue_status_id')->references('id')->on('queue_statuses')->onDelete('cascade');
            $table->foreign('office_service_id')->references('id')->on('office_services')->onDelete('set null');
            $table->foreign('counter_id')->references('id')->on('counters')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->index(['queue_status_id', 'time_start']);
            $table->index(['queue_no', 'created_at']);
            $table->index(['counter_id', 'queue_status_id']);
            $table->index('office_service_id');
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->string('status')->default('Draft');
            $table->string('type')->nullable();
            $table->string('icon')->nullable();
            $table->string('icon_color')->nullable();
            $table->string('icon_bg_color')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['status', 'scheduled_at']);
            $table->index('expires_at');
        });

        Schema::create('displays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('status')->default('Active');
            $table->string('connection_status')->default('Disconnected');
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->unsignedBigInteger('office_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('office_id')->references('id')->on('offices')->onDelete('cascade');

            $table->index(['office_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('displays');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('queues');
        Schema::dropIfExists('queue_statuses');
        Schema::dropIfExists('counter_user_logs');
        Schema::dropIfExists('counters');
        Schema::dropIfExists('service_requirements');
        Schema::dropIfExists('office_services');
        Schema::dropIfExists('offices');
    }
};
