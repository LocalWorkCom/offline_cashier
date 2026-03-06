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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_fees', 8, 3)->change();
            $table->decimal('total_price_befor_tax', 8, 3)->change();
            $table->decimal('total_price_before_coupon', 8, 3)->change();
            $table->decimal('total_price_after_tax', 8, 3)->change();
            $table->decimal('service_fees', 8, 3)->change();
            $table->decimal('coupon_value', 8, 3)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revert back (example: DECIMAL(8,2), adjust if your old type was different)
            $table->decimal('delivery_fees', 8, 2)->change();
            $table->decimal('total_price_befor_tax', 8, 2)->change();
            $table->decimal('total_price_before_coupon', 8, 2)->change();
            $table->decimal('total_price_after_tax', 8, 2)->change();
            $table->decimal('service_fees', 8, 2)->change();
            $table->decimal('coupon_value', 8, 2)->change();
        });
    }
};
