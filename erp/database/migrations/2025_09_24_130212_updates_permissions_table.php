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
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'module_ids')) {
                $table->dropColumn('module_ids'); // remove JSON field
            }

            if (!Schema::hasColumn('permissions', 'is_active')) {
                $table->tinyInteger('is_active')->default(1)->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (!Schema::hasColumn('permissions', 'module_ids')) {
                $table->json('module_ids')->nullable()->after('is_global');
            }
        });
    }
};
