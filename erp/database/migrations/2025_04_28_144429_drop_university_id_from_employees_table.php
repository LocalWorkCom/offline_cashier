<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUniversityIdFromEmployeesTable extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['university_id']);

            $table->dropColumn('university_id');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('university_id')->nullable(); // Adjust type if needed
            $table->foreign('university_id')->references('id')->on('universities')->onDelete('set null');

        });
    }
}
