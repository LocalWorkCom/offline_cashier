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
        Schema::table('invoice_details', function (Blueprint $table) {
            $table->decimal('tax', 8, 3)->change();
            $table->decimal('service_fees', 8, 3)->change();
            $table->decimal('coupon_value', 8, 3)->change();
            $table->decimal('total_before_tax', 8, 3)->change();
            $table->decimal('total_before_coupon', 8, 3)->change();
            $table->decimal('total_after_tax', 8, 3)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_details', function (Blueprint $table) {
            // assuming they were DECIMAL(8,2) originally
            $table->decimal('tax', 8, 2)->change();
            $table->decimal('service_fees', 8, 2)->change();
            $table->decimal('coupon_value', 8, 2)->change();
            $table->decimal('total_before_tax', 8, 2)->change();
            $table->decimal('total_before_coupon', 8, 2)->change();
            $table->decimal('total_after_tax', 8, 2)->change();
        });
    }
};
