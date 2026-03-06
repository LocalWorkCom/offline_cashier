<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class UpdateViolationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('violation_types', function (Blueprint $table) {
            // Rename the existing `name` column to `name_ar`
            $table->renameColumn('name', 'name_ar');
            
            // Add the `name_en` column for the English name
            $table->string('name_en');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('violation_types', function (Blueprint $table) {
            // Rename `name_ar` back to `name`
            $table->renameColumn('name_ar', 'name');
            
            // Remove the `name_en` column
            $table->dropColumn('name_en');
        });
    }
}
