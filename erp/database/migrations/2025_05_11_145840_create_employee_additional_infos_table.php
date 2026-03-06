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
        Schema::create('employee_additional_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');

            $table->text('additional_description')->nullable();
            $table->string('referral_source')->nullable(); // e.g., Friend, Ad, Employee
            $table->string('languages_spoken')->nullable(); // comma-separated values
            $table->text('visa_information')->nullable();
            $table->boolean('is_residency_transferable')->default(false);
            $table->text('tasks_and_instructions')->nullable();
            $table->string('criminal_record_file')->nullable(); // police clearance document
            $table->string('drug_test_report_file')->nullable(); // drug test document

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_additional_infos');
    }
};
