<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // First, drop the column if it exists and is JSON (we’ll recreate it)
            if (Schema::hasColumn('categories', 'parent_id')) {
                // Check if column type is JSON and modify
                $columnType = DB::select("SHOW COLUMNS FROM categories WHERE Field = 'parent_id'")[0]->Type ?? null;

                if (str_contains(strtolower($columnType), 'json')) {
                    // Drop JSON column
                    $table->dropColumn('parent_id');
                }
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            // Add correct type if missing
            if (!Schema::hasColumn('categories', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            }

            // Add FK
            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
