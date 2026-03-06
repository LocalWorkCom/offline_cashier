<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('excuse_requests', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('employee_id');
            
            // Excuse details
            $table->date('date')->nullable();
            $table->time('start_time')->nullable(); 
            $table->time('end_time')->nullable(); 
            $table->string('reason')->nullable(); 
            
            // Approval process
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); 
            $table->date('approved_date')->nullable(); 
            $table->unsignedBigInteger('approved_by')->nullable(); 
            
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null'); // Updated reference
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excuse_requests');
    }
};
