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
        Schema::create('payroll_sheet_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_sheet_id');
            $table->unsignedBigInteger('employee_id');

            // salary breakdown
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('overtime', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('bonuses', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);

            $table->json('calculation_details')->nullable();

            // new fields
            $table->enum('status', ['pending', 'reviewed', 'needs_correction', 'approved'])
                ->default('pending');
            $table->text('comments')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_sheet_items');
    }
};
