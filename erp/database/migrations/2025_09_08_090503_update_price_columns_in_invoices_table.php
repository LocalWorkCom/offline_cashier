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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('tax', 8, 3)->change();
            $table->decimal('service_fees', 8, 3)->change();
            $table->decimal('delivery_fees', 8, 3)->change();
            $table->decimal('tax_percentage', 8, 3)->change();
            $table->decimal('service_percentage', 8, 3)->change();
            $table->decimal('coupon_value', 8, 3)->change();
            $table->decimal('total_before_tax', 8, 3)->change();
            $table->decimal('total_before_coupon', 8, 3)->change();
            $table->decimal('total_after_tax', 8, 3)->change();
            $table->decimal('original_price', 8, 3)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // assuming old columns were DECIMAL(8,2) or DOUBLE(8,2)
            $table->decimal('tax', 8, 2)->change();
            $table->decimal('service_fees', 8, 2)->change();
            $table->decimal('delivery_fees', 8, 2)->change();
            $table->decimal('tax_percentage', 8, 2)->change();
            $table->decimal('service_percentage', 8, 2)->change();
            $table->decimal('coupon_value', 8, 2)->change();
            $table->decimal('total_before_tax', 8, 2)->change();
            $table->decimal('total_before_coupon', 8, 2)->change();
            $table->decimal('total_after_tax', 8, 2)->change();
            $table->decimal('original_price', 8, 2)->change();
        });
    }
};
