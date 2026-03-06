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
        Schema::create('employee_leave_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('leave_type_id')->nullable();
            $table->unsignedBigInteger('position_id')->nullable();
            $table->unsignedBigInteger('leave_request_id')->nullable();
            $table->date('date')->nullable();
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->integer('day_count')->default(0)->nullable();
            $table->integer('day_paid')->default(0)->nullable();
            $table->integer('day_unpaid')->default(0)->nullable();
            $table->string('resone')->nullable();
            $table->timestamps();

            //relation
            $table->foreign('employee_id')->references('id')->on('employees')->onUpdate('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onUpdate('cascade');
            $table->foreign('position_id')->references('id')->on('positions')->onUpdate('cascade');
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leave_logs');
    }
};
