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
        Schema::create('employee_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
        
            $table->unsignedBigInteger('filed_of_study_id')->nullable();
            $table->foreign('filed_of_study_id')->references('id')->on('filed_of_studies')->onDelete('set null');
        
            $table->unsignedBigInteger('education_level_id')->nullable();
            $table->foreign('education_level_id')->references('id')->on('education_levels')->onDelete('set null');
        
            $table->unsignedBigInteger('university_id')->nullable();
            $table->foreign('university_id')->references('id')->on('universities')->onDelete('set null');
        
            $table->year('graduation_year');
            $table->string('degree_certificate')->nullable();
            $table->text('additional_certifications')->nullable();
            $table->string('certification_documents')->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_educations');
    }
};
