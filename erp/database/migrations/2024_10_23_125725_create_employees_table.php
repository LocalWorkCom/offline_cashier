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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            
            // Unique identifiers
            $table->string('employee_code')->unique();
            $table->unsignedBigInteger('user_id')->unique()->nullable(); 
            
            // Basic Info
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            
            // Bio Information
            $table->string('gender', 10)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('national_id')->unique()->nullable();
            $table->string('passport_number')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('address_en')->nullable(); 
            $table->string('address_ar')->nullable(); 
            $table->string('nationality_en')->nullable();
            $table->string('nationality_ar')->nullable();
            
            // Work-related Info
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('position_id')->nullable();
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->date('hire_date')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->decimal('assurance_salary', 10, 2)->nullable(); 
            $table->string('assurance_number')->nullable(); 
            $table->string('bank_account')->nullable();
            $table->string('employment_type')->nullable(); 
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            
            $table->boolean('is_biometric')->default(false);
            $table->string('biometric_id')->nullable();

           

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('created_by');  
            $table->foreign('created_by')->references('id')->on('users');
            
            $table->unsignedBigInteger('modified_by')->nullable();  
            $table->foreign('modified_by')->references('id')->on('users');
            
            $table->unsignedBigInteger('deleted_by')->nullable();  
            $table->foreign('deleted_by')->references('id')->on('users');
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
