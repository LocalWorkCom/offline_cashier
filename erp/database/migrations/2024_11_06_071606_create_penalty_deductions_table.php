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
        // Schema::create('penalty_deductions', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('penalty_id')->constrained('penalties')->cascadeOnUpdate()->cascadeOnDelete();
        //     $table->foreignId('employee_id')->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
        //     $table->decimal('deduction_amount', 8, 2)->default(0);
        //     $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
        //     $table->foreignId('modified_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
        //     $table->foreignId('deleted_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
        //     $table->timestamps();
        //     $table->softDeletes();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalty_deductions');
    }
};
