<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employee_payroll_settings', function (Blueprint $table) {
            $table->dropColumn(['salary_weekday', 'salary_monthday']);
        });
    }

    public function down()
    {
        Schema::table('employee_payroll_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('salary_weekday')->nullable();
            $table->unsignedTinyInteger('salary_monthday')->nullable();
        });
    }
};
