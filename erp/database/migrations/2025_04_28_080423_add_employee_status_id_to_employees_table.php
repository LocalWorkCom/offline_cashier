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
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_status_id')->nullable()->after('id'); // or after any other column you want
            $table->foreign('employee_status_id')->references('id')->on('employee_status')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['employee_status_id']);
            $table->dropColumn('employee_status_id');
        });
    }
};
