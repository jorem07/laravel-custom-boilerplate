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
        Schema::create('office_service_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('list');
            $table->unsignedBigInteger('office_service_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('office_service_id')->references('id')->on('office_services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_service_requirements');
    }
};
