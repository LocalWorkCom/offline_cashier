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
        Schema::table('employee_salary_details', function (Blueprint $table) {
            $table->dropColumn('payment_frequency');
            $table->dropColumn('payment_type');
            $table->dropColumn('is_bounce_allowance');
            $table->boolean('is_allowance')->default(1)->nullable();
            $table->boolean('is_bonus')->default(1)->nullable();
            $table->boolean('is_commision')->default(1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_salary_details', function (Blueprint $table) {
            //
        });
    }
};
