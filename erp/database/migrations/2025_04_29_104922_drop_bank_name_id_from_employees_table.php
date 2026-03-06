<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropBankNameIdFromEmployeesTable extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['bank_name_id']);

            $table->dropColumn('bank_name_id');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('bank_name_id')->nullable(); // Adjust type if needed
            $table->foreign('bank_name_id')->references('id')->on('bank_names')->onDelete('set null');

        });
    }
}
