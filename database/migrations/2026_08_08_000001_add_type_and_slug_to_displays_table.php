<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('displays', function (Blueprint $table) {
            if (!Schema::hasColumn('displays', 'layout_type')) {
                $table->string('layout_type')->default('standard')->after('status');
            }
            if (!Schema::hasColumn('displays', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('displays', function (Blueprint $table) {
            if (Schema::hasColumn('displays', 'layout_type')) {
                $table->dropColumn('layout_type');
            }
            if (Schema::hasColumn('displays', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
