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
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('employee_id')->nullable();
            
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees')
                  ->onUpdate('cascade')
                  ->nullOnDelete();

            // Rating (1-5 scale)
            $table->tinyInteger('rating')->unsigned()->comment('1: Poor, 2: Unsatisfactory, 3: Satisfactory, 4: Very Satisfactory, 5: Outstanding');
            
            // Review content
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->text('additional_comments')->nullable();
            
            // Tracking fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->string('created_by_type')->nullable();
            $table->string('modified_by_type')->nullable();
            $table->string('deleted_by_type')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });
        
        Schema::dropIfExists('performance_reviews');
    }
};