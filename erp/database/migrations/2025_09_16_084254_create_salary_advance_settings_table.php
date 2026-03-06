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
        Schema::create('salary_advance_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('max_advance_limit');
            $table->enum('percentage_type', ['base_salary', 'total_salary'])->default('total_salary');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->foreign('created_by')->references('id')->on('employees');
            $table->foreign('modified_by')->references('id')->on('employees');
            $table->foreign('deleted_by')->references('id')->on('employees');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_advance_settings');
    }
};
