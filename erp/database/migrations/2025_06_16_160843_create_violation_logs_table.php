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
        Schema::create('violation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('violation_id')->constrained();
            $table->unsignedBigInteger('old_penalty_id')->nullable();
            $table->unsignedBigInteger('new_penalty_id')->nullable();
            $table->unsignedInteger('old_order')->nullable();
            $table->unsignedInteger('new_order')->nullable();
            $table->string('action'); // updated / deleted
            $table->foreignId('changed_by')->constrained('employees');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violation_logs');
    }
};
