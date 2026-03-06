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
        Schema::create('employee_warnings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('hr_manager_id');
            $table->text('description');
            $table->date('issue_date');
            $table->text('consequences');
            $table->text('action_plan')->nullable();
            $table->string('hr_name')->nullable();
            $table->string('hr_signature')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('document_path')->nullable();
            $table->enum('acknowledgment_status', ['pending', 'acknowledged', 'refused', 'hr_confirmed'])->default('pending');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('hr_confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('hr_manager_id')->references('id')->on('employees')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_warnings');
    }
};
