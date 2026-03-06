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
            // Make name_en nullable
            $table->string('name_en')->nullable()->change();
            
            // Make name_ar required (remove nullable)
            $table->string('name_ar')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('storage_locations', function (Blueprint $table) {
            // Revert name_en to not nullable
            $table->string('name_en')->nullable(false)->change();
            
            // Revert name_ar to nullable
            $table->string('name_ar')->nullable()->change();
        });
    }
};