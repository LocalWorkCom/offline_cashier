<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banking_information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('bank_name');
            $table->string('bank_account_number');
            $table->string('bank_iban');
            $table->boolean('is_payroll_account');  // true for Bank 1, false for Bank 2
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banking_information');
    }
};
