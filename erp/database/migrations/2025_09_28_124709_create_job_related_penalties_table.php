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
        Schema::create('job_related_penalties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('curr_possition_id')->nullable();
            $table->unsignedBigInteger('new_possition_id')->nullable();
            $table->unsignedBigInteger('curr_department_id')->nullable();
            $table->unsignedBigInteger('new_department_id')->nullable();
            $table->unsignedBigInteger('curr_branch_id')->nullable();
            $table->unsignedBigInteger('new_branch_id')->nullable();
            $table->unsignedBigInteger('privilege_type_id')->nullable();
            $table->date('effective_date');
            $table->string('reason')->nullable();
            $table->string('restricted_system')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('type', ['demotion', 'transfer', 'loss_job', 'restrict_system']);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->string('modified_by_type')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->string('deleted_by_type')->nullable();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('curr_possition_id')->references('id')->on('positions')->onDelete('cascade')->nullOnDelete();
            $table->foreign('new_possition_id')->references('id')->on('positions')->onDelete('cascade')->nullOnDelete();
            $table->foreign('curr_department_id')->references('id')->on('departments')->onDelete('cascade')->nullOnDelete();
            $table->foreign('new_department_id')->references('id')->on('departments')->onDelete('cascade')->nullOnDelete();
            $table->foreign('curr_branch_id')->references('id')->on('branches')->onDelete('cascade')->nullOnDelete();
            $table->foreign('new_branch_id')->references('id')->on('branches')->onDelete('cascade')->nullOnDelete();
            $table->foreign('privilege_type_id')->references('id')->on('privilege_types')->onDelete('cascade')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_related_penalties');
    }
};
