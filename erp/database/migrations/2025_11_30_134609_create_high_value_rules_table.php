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
        Schema::create('high_value_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->decimal('financial_limit', 15, 2)->nullable(); // e.g., 50000
            $table->string('DRR')->comment('Deposit Ratio Requirement')->nullable();
            $table->unsignedTinyInteger('max_deposit_percentage')->nullable(); // optional max deposit
            $table->boolean('active')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('high_value_rules');
    }
};
