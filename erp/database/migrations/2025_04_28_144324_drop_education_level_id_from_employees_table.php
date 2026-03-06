<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropEducationLevelIdFromEmployeesTable extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // First, drop the foreign key constraint
            $table->dropForeign(['education_level_id']);
            // Then, drop the column itself
            $table->dropColumn('education_level_id');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Re-add the column
            $table->unsignedBigInteger('education_level_id')->nullable();
            // Re-add the foreign key constraint
            $table->foreign('education_level_id')->references('id')->on('education_levels')->onDelete('set null');
        });
    }
}
