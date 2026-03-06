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
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('deduction_type', ['exact', 'partial', 'fixed', 'double'])->default('exact');
            $table->integer('threshold_minutes')->nullable()->comment('Used for fixed deduction, e.g. 120 min');
            $table->integer('round_unit')->nullable()->comment('Used for partial deduction, e.g. 15/30/60 minutes');
            $table->boolean('allow_manual_override')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
    
            $table->foreign('created_by')->references('id')->on('employees');
            $table->foreign('updated_by')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
