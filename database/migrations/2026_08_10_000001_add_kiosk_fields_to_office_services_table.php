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
        Schema::table('office_services', function (Blueprint $table) {
            if (!Schema::hasColumn('office_services', 'subtitle')) {
                $table->string('subtitle')->nullable()->after('name');
            }
            if (!Schema::hasColumn('office_services', 'icon')) {
                $table->string('icon')->nullable()->after('subtitle');
            }
            if (!Schema::hasColumn('office_services', 'icon_color')) {
                $table->string('icon_color')->nullable()->after('icon');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('office_services', function (Blueprint $table) {
            if (Schema::hasColumn('office_services', 'icon_color')) {
                $table->dropColumn('icon_color');
            }
            if (Schema::hasColumn('office_services', 'icon')) {
                $table->dropColumn('icon');
            }
            if (Schema::hasColumn('office_services', 'subtitle')) {
                $table->dropColumn('subtitle');
            }
        });
    }
};
