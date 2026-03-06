<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('violation_type_id')->constrained()->onDelete('cascade');  // Foreign key to ViolationType table
            $table->timestamp('violation_date');  // Date and time of the violation
            $table->string('violation_location')->nullable();  // Location of violation (optional)
            $table->text('description');  // Description of the violation
            $table->text('penalty')->nullable();  // Penalty or corrective action taken (optional)
            $table->enum('status', ['pending', 'resolved'])->default('pending');  // Status of the violation
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
