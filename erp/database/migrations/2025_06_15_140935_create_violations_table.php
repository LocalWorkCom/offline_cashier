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
        Schema::dropIfExists('violations');
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Violation name
            $table->integer('max_repeat')->default(1); // Max allowed repetitions
            $table->integer('within_period')->default(30); // In days
            $table->unsignedBigInteger('created_by')->default(1);
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();

            $table->timestamps();
            $table->foreign('created_by')->references('id')->on('employees');
            $table->foreign('modified_by')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
