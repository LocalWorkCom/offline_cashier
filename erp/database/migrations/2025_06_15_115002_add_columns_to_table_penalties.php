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
        Schema::table('penalties', function (Blueprint $table) {
            $table->enum('calculation_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('amount', 10, 2)->nullable();
            $table->enum('allowance_type', [
                'housing',
                'transportation',
                'meal',
                'medical',
                'mobile',
                'internet',
                'shift',
                'hardship',
                'travel',
                'uniform',
            ])->nullable()->default('housing');
            $table->enum('bonus_type', [
                'performance_bonus',
                'attendance_bonus',
                'sales_commission',
                'holiday_bonus',
                'year_end_bonus',
                'referral_bonus',
                'sign_on_bonus',
                'retention_bonus',
                'project_completion_bonus',
                'safety_bonus',
            ])->nullable()->default('performance_bonus');
            $table->foreignId('violation_type_id')->nullable();
            $table->date('effective_date');
            $table->date('end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
            //
        });
    }
};
