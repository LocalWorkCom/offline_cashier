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
        Schema::create('employee_opening_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('cashier_machine_id')->nullable();
            $table->unsignedBigInteger('employee_schedule_id')->nullable();
            $table->decimal('open_cash', 8,2)->default(0)->nullable();
            $table->decimal('open_visa', 8,2)->default(0)->nullable();
            $table->decimal('close_cash', 8,2)->default(0)->nullable();
            $table->decimal('close_visa', 8,2)->default(0)->nullable();
            $table->decimal('real_cash', 8,2)->default(0)->nullable();
            $table->decimal('real_visa', 8,2)->default(0)->nullable();
            $table->decimal('deficit_cash', 8,2)->default(0)->nullable();
            $table->decimal('deficit_visa', 8,2)->default(0)->nullable();
            $table->integer('type')->default(1)->comment('1 for open, 2 for close');
            $table->date('date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onUpdate('cascade');
            $table->foreign('cashier_machine_id')->references('id')->on('cashier_machines')->onUpdate('cascade');
            $table->foreign('employee_schedule_id')->references('id')->on('employee_schedules')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onUpdate('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_opening_balances');
    }
};
