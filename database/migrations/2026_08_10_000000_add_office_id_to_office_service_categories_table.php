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
        if (!Schema::hasColumn('office_service_categories', 'office_id')) {
            Schema::table('office_service_categories', function (Blueprint $table) {
                $table->unsignedBigInteger('office_id')->nullable()->after('id');
                $table->foreign('office_id')->references('id')->on('offices')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('office_service_categories', 'office_id')) {
            Schema::table('office_service_categories', function (Blueprint $table) {
                $table->dropForeign(['office_id']);
                $table->dropColumn('office_id');
            });
        }
    }
};
