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
        Schema::dropIfExists('announcements');
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('Published');
            $table->unsignedBigInteger('announcement_status_id')->nullable();
            $table->string('type')->default('info');
            $table->string('icon')->nullable();
            $table->string('icon_color')->nullable();
            $table->string('icon_bg_color')->nullable();
            $table->unsignedBigInteger('office_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('publish_schedule')->nullable();
            $table->dateTime('expire_schedule')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('announcement_status_id')->references('id')->on('announcement_statuses')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('office_id')->references('id')->on('offices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
