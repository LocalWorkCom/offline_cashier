<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_leave_logs', function (Blueprint $table) {
            $table->float('deduction_value')->default(1)->nullable()->after('day_unpaid');
            $table->float('deduction_days')->default(0)->nullable()->after('deduction_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave_logs', function (Blueprint $table) {
            $table->dropColumn(['deduction_value', 'deduction_days']);
        });
    }
};
