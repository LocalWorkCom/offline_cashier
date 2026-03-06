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
        Schema::table('storage_locations', function (Blueprint $table) {
            // Rename name to name_en
            $table->renameColumn('name', 'name_en');
            
            // Add name_ar column
            $table->string('name_ar')->nullable();
            
            // Add description columns
            $table->string('description_ar')->nullable()->after('name_ar');
            $table->string('description_en')->nullable()->after('description_ar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('storage_locations', function (Blueprint $table) {
            // Reverse the column changes
            $table->renameColumn('name_en', 'name');
            $table->dropColumn(['name_ar', 'description_ar', 'description_en']);
        });
    }
};