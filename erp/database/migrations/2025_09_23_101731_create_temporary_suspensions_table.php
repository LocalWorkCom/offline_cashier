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
        Schema::create('temporary_suspensions', function (Blueprint $table) {
            $table->id();

            // Employee Information
            $table->unsignedBigInteger('employee_id')->nullable();

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onUpdate('cascade')
                ->nullOnDelete();
            // Suspension Period
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('suspension_duration'); // Number of days

            // Suspension Details
            $table->text('reason');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');

            // Supporting Documents
            $table->string('supporting_documents')->nullable(); // File path or JSON of file paths

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index('approval_status');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('temporary_suspensions', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });
        Schema::dropIfExists('temporary_suspensions');
    }
};
