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
            $table->decimal('open_cash', 8,2)->default(0)->nullable();
            $table->decimal('open_visa', 8,2)->default(0)->nullable();
            $table->decimal('close_cash', 8,2)->default(0)->nullable();
            $table->decimal('close_visa', 8,2)->default(0)->nullable();
            $table->decimal('real_cash', 8,2)->default(0)->nullable();
            $table->decimal('real_visa', 8,2)->default(0)->nullable();
            $table->integer('type')->default(1)->comment('1 for open, 2 for close');
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
