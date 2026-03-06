<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeePayrollSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_payroll_settings', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // Foreign key to the 'employees' table
            $table->enum('salary_type', ['daily', 'weekly', 'monthly']); // ENUM for salary type
            $table->decimal('salary_value', 10, 2)->default(0); // Decimal value for salary
            $table->date('effective_from'); // Effective from date
            $table->date('effective_to')->nullable(); // Effective to date, nullable
            $table->timestamps(); // created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_payroll_settings');
    }
}
