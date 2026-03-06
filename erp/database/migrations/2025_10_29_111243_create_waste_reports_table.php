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
        Schema::create('waste_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('report_number');
            $table->string('name');
            $table->date('date');
            $table->time('time');
            $table->enum('status', ['pending','Being Audited', 'confirmed', 'rejected'])->default('pending');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('first_quality_officer_id')->nullable();
            $table->unsignedBigInteger('second_quality_officer_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('first_quality_officer_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('second_quality_officer_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_reports');
    }
};
