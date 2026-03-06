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
        Schema::table('branch_menus', function (Blueprint $table) {
            // Check if the foreign key exists before trying to drop it
            if (Schema::hasColumn('branch_menus', 'created_by')) {
                $table->dropForeign(['created_by']);
            }

            // Add columns if they don't exist
            if (!Schema::hasColumn('branch_menus', 'created_by')) {
                $table->integer('created_by')->nullable()->change();
            }

            if (!Schema::hasColumn('branch_menus', 'modified_by')) {
                $table->integer('modified_by')->nullable()->change();
            }

            if (!Schema::hasColumn('branch_menus', 'deleted_by')) {
                $table->integer('deleted_by')->nullable()->change();
            }

            // Add type columns
            if (!Schema::hasColumn('branch_menus', 'created_by_type')) {
                $table->string('created_by_type')->nullable()->after('created_by');
            }

            if (!Schema::hasColumn('branch_menus', 'modified_by_type')) {
                $table->string('modified_by_type')->nullable()->after('modified_by');
            }

            if (!Schema::hasColumn('branch_menus', 'deleted_by_type')) {
                $table->string('deleted_by_type')->nullable()->after('deleted_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_menus', function (Blueprint $table) {
            // Remove the added columns in reverse order
            $table->dropColumn([
                'deleted_by_type',
                'modified_by_type',
                'created_by_type',
                'deleted_by',
                'modified_by',
                'created_by',
            ]);
        });
    }
};