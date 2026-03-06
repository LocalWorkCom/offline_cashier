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
        Schema::create('branch_safe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onUpdate('cascade');
            $table->json('balances_ids')->nullable(); 
            $table->foreignId('cashier_id')->constrained('employees')->onUpdate('cascade'); 
            $table->decimal('cash_amount', 15, 2)->default(0);
            $table->decimal('visa_amount', 15, 2)->default(0);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('employees')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('employees')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('employees')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_safe');
    }
};
