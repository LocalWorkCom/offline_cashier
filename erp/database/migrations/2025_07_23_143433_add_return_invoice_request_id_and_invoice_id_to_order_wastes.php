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
        Schema::table('order_waste_logs', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable();
            $table->foreignId('return_invoice_request_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_waste_logs', function (Blueprint $table) {
            //
        });
    }
};
