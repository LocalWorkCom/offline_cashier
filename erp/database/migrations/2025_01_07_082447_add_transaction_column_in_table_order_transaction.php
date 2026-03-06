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
        Schema::table('order_transactions', function (Blueprint $table) {
            $table->string('payment_gateway_reference')->nullable();
            $table->date('payment_gateway_date')->nullable();
            $table->string('payment_gateway_currency')->nullable();
            $table->string('payment_gateway_status')->nullable();
            $table->string('payment_gateway_method')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_transactions', function (Blueprint $table) {
            //
        });
    }
};
