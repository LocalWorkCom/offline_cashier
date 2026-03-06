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

        Schema::create('employee_violations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('violation_penalty_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('date');

            $table->unsignedBigInteger('created_by')->default(1);
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            // Foreign key constraints
            $table->foreign('created_by')->references('id')->on('employees');
            $table->foreign('modified_by')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('employees')->nullOnDelete();

            $table->foreign('violation_penalty_id')->references('id')->on('violation_penalties')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_violations');
    }
};
