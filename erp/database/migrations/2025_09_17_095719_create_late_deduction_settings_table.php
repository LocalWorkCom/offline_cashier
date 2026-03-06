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
        // Drop old table if it exists
        Schema::dropIfExists('attendance_settings');

        // Create new table
        Schema::create('late_deduction_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('deduction_mode', [
                'exact',      // Exact Late Time Deduction
                'partial',    // Partial Deduction (rounding to intervals)
                'fixed',      // Fixed Deduction (full/half-day)
                'double'      // Double Penalty
            ]);

            // For partial mode → store interval (like 15, 30, 60 mins)
            $table->integer('partial_interval')->nullable();

            // For fixed mode → threshold in minutes before applying full-day
            $table->integer('fixed_threshold')->nullable();

            // Whether to deduct from basic or total salary
            $table->enum('deduct_from', ['basic', 'total'])->default('basic');

            $table->boolean('notify_employee')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('late_deduction_settings');

        // (Optional) If you ever want to recreate old table:
        // Schema::create('attendance_settings', function (Blueprint $table) {
        //     $table->id();
        //     $table->timestamps();
        // });
    }
};
