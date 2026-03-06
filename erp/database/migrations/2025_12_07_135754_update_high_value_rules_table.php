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
        Schema::table('high_value_rules', function (Blueprint $table) {
            // Step 1: rename the column
            $table->renameColumn('name', 'name_en');
        });

        Schema::table('high_value_rules', function (Blueprint $table) {
            // Step 2: add new column and unique constraints
            $table->string('name_ar')->after('name_en');
            $table->unique('name_en');
            $table->unique('name_ar');
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
