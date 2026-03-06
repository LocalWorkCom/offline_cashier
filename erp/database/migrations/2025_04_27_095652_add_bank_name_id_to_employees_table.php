<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBankNameIdToEmployeesTable extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('bank_name_id')->nullable()->after('id'); // or after any other column you want
            $table->foreign('bank_name_id')->references('id')->on('bank_names')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['bank_name_id']);
            $table->dropColumn('bank_name_id');
        });
    }
}
