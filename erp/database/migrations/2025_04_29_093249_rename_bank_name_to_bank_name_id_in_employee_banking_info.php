<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('employee_banking_info', function (Blueprint $table) {
            $table->dropColumn('bank_name');
            $table->unsignedBigInteger('bank_name_id')->after('id'); // adjust position as needed

            // Optional: add foreign key constraint
            $table->foreign('bank_name_id')->references('id')->on('bank_names')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('employee_banking_info', function (Blueprint $table) {
            $table->dropForeign(['bank_name_id']); // only if you added foreign key
            $table->dropColumn('bank_name_id');
            $table->string('bank_name')->after('id');
        });
    }
};
