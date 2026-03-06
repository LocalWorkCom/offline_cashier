<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEthnicBackgroundIdToEmployeesTable extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('ethnic_background_id')->nullable()->after('id'); // or after any other column you want
            $table->foreign('ethnic_background_id')->references('id')->on('ethnic_backgrounds')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['ethnic_background_id']);
            $table->dropColumn('ethnic_background_id');
        });
    }
}
