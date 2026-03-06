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
        Schema::create('termination_of_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->unique();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('reason');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->timestamp('last_day');
            $table->unsignedBigInteger('severance_package');
            $table->enum('approval_workflow', ['Pending', 'Approved', 'Rejected']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('termination_of_services');
    }
};
