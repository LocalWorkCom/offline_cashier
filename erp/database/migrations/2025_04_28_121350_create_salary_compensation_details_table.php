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
        Schema::create('salary_compensation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('base_salary', 10, 2);  // Base salary
            $table->decimal('salary_work_permit', 10, 2);  // Salary as per work permit
            $table->enum('payment_frequency', ['Monthly', 'Weekly', 'Bi-weekly', 'Yearly']);  // Payment frequency
            $table->enum('payment_type', ['Fixed', 'Hourly', 'Commission']);  // Payment type
            $table->decimal('annual_salary', 15, 2);  // Annual salary
            $table->string('currency', 3);  // Currency code (e.g., USD, EUR)
            $table->boolean('is_bounce_allowance');
            $table->text('currency_code');
            $table->decimal('commission_amount', 10, 2)->nullable();  // Commission amount
            $table->enum('commission_type', ['Percentage', 'Fixed Amount'])->nullable();  // Commission type
            $table->enum('insurance_registered', ['Yes', 'No']);  // Insurance registration
            $table->decimal('insurance_subscription_amount', 10, 2)->nullable();  // Insurance subscription amount
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salary_details');
    }
};
