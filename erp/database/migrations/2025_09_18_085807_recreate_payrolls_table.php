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
        // Drop existing payrolls table
        Schema::dropIfExists('payrolls');

        // Recreate payrolls table
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('payroll_sheet_id');
            $table->unsignedBigInteger('payroll_sheet_item_id');

            $table->decimal('final_salary', 12, 2);
            $table->date('salary_date'); // finalized payment date

            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('payroll_sheet_id')->references('id')->on('payroll_sheets')->onDelete('cascade');
            $table->foreign('payroll_sheet_item_id')->references('id')->on('payroll_sheet_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
