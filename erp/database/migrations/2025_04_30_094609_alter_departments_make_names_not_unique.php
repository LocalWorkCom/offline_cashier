<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDepartmentsMakeNamesNotUnique extends Migration
{
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropUnique(['name_ar']);
            $table->dropUnique(['name_en']);
        });
    }

    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->unique('name_ar');
            $table->unique('name_en');
        });
    }
}
