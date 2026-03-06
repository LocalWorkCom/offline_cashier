<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('salary_advance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained(); // Reference to the employee
            $table->decimal('amount', 8, 2); // Requested salary advance amount
            $table->text('reason'); // Reason for the advance
            $table->enum('status', ['submitted', 'approved', 'rejected'])->default('submitted'); // Track approval status
            $table->text('hr_comment')->nullable(); // HR rejection reason or comment
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_type')->nullable();

            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('updated_by_type')->nullable();

            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_advance_requests');
    }
};
