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
        Schema::create('purchasing_budgets', function (Blueprint $table) {
            $table->id();

            $table->tinyInteger('month')->unsigned();  
            $table->smallInteger('year')->unsigned(); 

            // Initial defined budget
            $table->decimal('base_amount', 15, 2);

            // Total amount added after creation
            $table->decimal('increase_amount', 15, 2)->default(0);

            // Number of times the budget was increased
            $table->integer('increase_count')->default(0);

            // Remaining balance (base + increases - deductions)
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->string('notes')->nullable();
                        $table->boolean('is_active')->nullable();


            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // One budget per month/year
            $table->unique(['month', 'year']);

            $table->foreign('created_by')
                ->references('id')->on('employees')
                ->restrictOnDelete();
            $table->foreign('modified_by')
                ->references('id')->on('employees')
                ->restrictOnDelete();
            $table->foreign('deleted_by')
                ->references('id')->on('employees')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchasing_budgets');
    }
};
