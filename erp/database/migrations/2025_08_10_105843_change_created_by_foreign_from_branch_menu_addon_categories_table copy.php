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
         Schema::table('branch_menu_addon_categories', function (Blueprint $table) {
            // Drop foreign keys if they exist
            foreach (['modified_by', 'deleted_by'] as $col) {
                if (Schema::hasColumn('branch_menu_addon_categories', $col)) {
                    try {
                        $table->dropForeign([$col]);
                    } catch (\Exception $e) {
                        // No foreign key exists, ignore
                    }
                }
            }

            // Handle modified_by
            if (Schema::hasColumn('branch_menu_addon_categories', 'modified_by')) {
                $table->unsignedBigInteger('modified_by')->nullable()->change();
            } else {
                $table->unsignedBigInteger('modified_by')->nullable();
            }

            // Handle deleted_by
            if (Schema::hasColumn('branch_menu_addon_categories', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->change();
            } else {
                $table->unsignedBigInteger('deleted_by')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_menu_addon_categories', function (Blueprint $table) {
            //
        });
    }
};
