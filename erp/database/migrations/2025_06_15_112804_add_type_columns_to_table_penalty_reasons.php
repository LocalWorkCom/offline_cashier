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
        Schema::table('penalty_reasons', function (Blueprint $table) {
            $table->string('code')->unique(); // Short code for the reason
            $table->enum('type', ['salary_deduction', 'fine', 'allowance_reduction', 'bonus_loss'])->default('salary_deduction');
            $table->boolean('requires_approval')->default(true);
            $table->text('punishment_ar')->nullable()->change();
            $table->text('punishment_en')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penalty_reasons', function (Blueprint $table) {
            //
        });
    }
};
