<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employee_payroll_settings', function (Blueprint $table) {
            // For weekly salary system: store which weekday (0=Sunday, 6=Saturday)
            $table->unsignedTinyInteger('salary_weekday')->nullable()
                  ->comment('Day of week for weekly salary (0=Sunday, 6=Saturday)');

            // For monthly salary system: store which day of the month (1-31)
            $table->unsignedTinyInteger('salary_monthday')->nullable()
                  ->comment('Day of month for monthly salary (1-31)');
        });
    }

    public function down()
    {
        Schema::table('employee_payroll_settings', function (Blueprint $table) {
            $table->dropColumn(['salary_weekday', 'salary_monthday']);
        });
    }
};
