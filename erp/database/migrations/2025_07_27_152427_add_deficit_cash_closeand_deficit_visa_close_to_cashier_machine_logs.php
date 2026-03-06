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
        Schema::table('cashier_machine_logs', function (Blueprint $table) {
            $table->decimal('deficit_cash_close', 8,2)->default(0)->nullable();
            $table->decimal('deficit_visa_close', 8,2)->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashier_machine_logs', function (Blueprint $table) {
            //
        });
    }
};
