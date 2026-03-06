<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {

            // Ensure correct column type
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->unsignedBigInteger('deleted_by')->nullable()->change();
            $table->unsignedBigInteger('modified_by')->nullable()->change();
        });

        // Add foreign keys only if not exist
        $foreignKeys = DB::select("
    SELECT CONSTRAINT_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'brands'
");

        $existingFKs = array_map(fn($fk) => $fk->CONSTRAINT_NAME, $foreignKeys);

        Schema::table('brands', function (Blueprint $table) use ($existingFKs) {

            if (!in_array('brands_created_by_foreign', $existingFKs)) {
                $table->foreign('created_by')
                    ->references('id')->on('employees')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            }

            if (!in_array('brands_deleted_by_foreign', $existingFKs)) {
                $table->foreign('deleted_by')
                    ->references('id')->on('employees')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            }

            if (!in_array('brands_modified_by_foreign', $existingFKs)) {
                $table->foreign('modified_by')
                    ->references('id')->on('employees')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
