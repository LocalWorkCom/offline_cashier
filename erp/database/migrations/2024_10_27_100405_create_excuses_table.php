<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('excuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('excuse_request_id')->nullable(); 
            $table->string('reason'); 
            $table->date('date'); 
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->time('requested_time')->nullable(); 
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->date('approved_date')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('excuse_request_id')->references('id')->on('excuse_requests')->onDelete('set null'); 
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excuses');
    }
};
