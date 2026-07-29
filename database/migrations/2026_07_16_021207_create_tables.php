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

        Schema::create('office_service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('office_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('office_id')->nullable();
            $table->unsignedBigInteger('office_service_category_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('office_id')->references('id')->on('offices')->onDelete('cascade');
            $table->foreign('office_service_category_id')->references('id')->on('office_service_categories')->onDelete('cascade');
        });

        Schema::create('counters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('office_service_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('office_service_id')->references('id')->on('office_services')->onDelete('cascade');
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
        });

        Schema::create('queue_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->string('queue_no');
            $table->unsignedBigInteger('queue_status_id');
            $table->unsignedBigInteger('office_service_id')->nullable();
            $table->unsignedBigInteger('counter_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->uuid();
            $table->string('token')->nullable();
            $table->dateTime('time_start');
            $table->dateTime('time_end')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('queue_status_id')->references('id')->on('queue_statuses')->onDelete('cascade');
            $table->foreign('office_service_id')->references('id')->on('office_services')->onDelete('cascade');
            $table->foreign('counter_id')->references('id')->on('counters')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
        Schema::dropIfExists('queue_statuses');
        Schema::dropIfExists('counter_user_logs');
        Schema::dropIfExists('counters');
        Schema::dropIfExists('office_services');
        Schema::dropIfExists('office_service_categories');
        Schema::dropIfExists('offices');
    }
};
