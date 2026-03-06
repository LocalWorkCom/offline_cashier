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
        Schema::create('cashier_setting_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('pos_id')->nullable();
            $table->decimal('min_total', 15, 2)->nullable();
            $table->decimal('max_total', 15, 2)->nullable();
            $table->integer('min_num')->nullable();
            $table->integer('max_num')->nullable();
            $table->time('auto_run_time')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Foreign key constraints (optional)
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('pos_id')->references('id')->on('cashier_machines')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashiersettinglog');
    }
};
