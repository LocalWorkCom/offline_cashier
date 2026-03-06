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
        Schema::table('order_addons', function (Blueprint $table) {
            $table->decimal('price_before_tax', 8, 3)->change();
            $table->decimal('price_before_coupon', 8, 3)->change();
            $table->decimal('price_after_tax', 8, 3)->change();
            $table->decimal('tax_value', 8, 3)->change();
            $table->decimal('service_fees', 8, 3)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_addons', function (Blueprint $table) {
            // revert to old type (double(8,2))
            $table->double('price_before_tax', 8, 2)->change();
            $table->double('price_before_coupon', 8, 2)->change();
            $table->double('price_after_tax', 8, 2)->change();
            $table->double('tax_value', 8, 2)->change();
            $table->double('service_fees', 8, 2)->change();
        });
    }
};
