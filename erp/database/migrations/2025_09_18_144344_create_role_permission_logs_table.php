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
        Schema::create('role_permission_logs', function (Blueprint $table) {
           $table->id();

            // Who did the action
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->string('causer_type')->nullable();

            // Action type
            $table->string('action');

            // Target employee
            $table->unsignedBigInteger('employee_id')->nullable();

            // Roles (if needed, keep single for now)
            $table->unsignedBigInteger('role_id')->nullable();

            // Store multiple permissions as JSON
            $table->json('permission_ids')->nullable();

            // Extra metadata (for flexibility)
            $table->json('extra_data')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permission_logs');
    }
};
