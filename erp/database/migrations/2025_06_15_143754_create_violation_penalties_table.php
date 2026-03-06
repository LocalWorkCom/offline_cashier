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
        Schema::create('violation_penalties', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('violation_id')->nullable();
            $table->unsignedBigInteger('penalty_id')->nullable();
            $table->integer('order_penalty')->default(1);

            $table->unsignedBigInteger('created_by')->default(1);
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('created_by')->references('id')->on('employees');
            $table->foreign('modified_by')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('violation_id')->references('id')->on('violations')->onDelete('cascade');
            $table->foreign('penalty_id')->references('id')->on('penalties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violation_penalties');
    }
};
