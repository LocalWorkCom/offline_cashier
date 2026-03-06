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
        Schema::create('company_policy_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_policy_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('viewed_at')->nullable();

            $table->foreign('company_policy_id')->references('id')->on('company_policies')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_policy_acknowledgements');
    }
};
