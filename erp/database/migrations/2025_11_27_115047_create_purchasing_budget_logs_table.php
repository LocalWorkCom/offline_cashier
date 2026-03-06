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
        Schema::create('purchasing_budget_logs', function (Blueprint $table) {
            $table->id();

            // Link to budget
            $table->unsignedBigInteger('purchasing_budget_id');

            // + or - amount applied to the budget
            $table->decimal('amount', 15, 2);

            // Entry type: creation, increase, deduction
            $table->enum('type', ['creation', 'increase', 'deduction']);

            // Description of why this movement happened

            $table->string('reference')->nullable();

            // Before and after balance snapshots
            $table->decimal('previous_remaining', 15, 2)->default(0);
            $table->decimal('new_remaining', 15, 2)->default(0);

            // User performing the action (Finance Manager or system)
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            // Relations
            $table->foreign('purchasing_budget_id')
                ->references('id')->on('purchasing_budgets')
                ->cascadeOnDelete();

            $table->foreign('created_by')
                ->references('id')->on('employees')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchasing_budget_logs');
    }
};
