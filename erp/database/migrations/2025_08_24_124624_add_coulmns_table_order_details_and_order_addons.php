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
        Schema::table('order_details', function (Blueprint $table) {

            $table->decimal('price_before_coupon', 8, 2)->after('price_befor_tax');
        });
         Schema::table('order_addons', function (Blueprint $table) {

            $table->decimal('price_before_coupon', 8, 2)->after('price_before_tax');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            //
        });
    }
};
