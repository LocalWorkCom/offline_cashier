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
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'module_ids')) {
                $table->dropColumn('module_ids'); // remove JSON field
            }

            if (!Schema::hasColumn('roles', 'module_id')) {
                $table->unsignedBigInteger('module_id')->nullable()->after('guard_name');
                $table->foreign('module_id')->references('id')->on('system_modules')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'module_id')) {
                $table->dropForeign(['module_id']);
                $table->dropColumn('module_id');
            }

            if (!Schema::hasColumn('roles', 'module_ids')) {
                $table->json('module_ids')->nullable()->after('is_global');
            }
        });
    }
};
