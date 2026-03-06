<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropFiledOfStudyIdFromEmployeesTable extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['filed_of_study_id']);

            $table->dropColumn('filed_of_study_id');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('filed_of_study_id')->nullable(); // Adjust type if needed
            $table->foreign('filed_of_study_id')->references('id')->on('filed_of_studies')->onDelete('set null');

        });
    }
}
