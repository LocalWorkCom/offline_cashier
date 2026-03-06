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
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropForeign('order_products_unit_id_foreign');
            $table->dropColumn('unit_id');

            $table->unsignedBigInteger('product_unit_id')->nullable();
            $table->foreign('product_unit_id')->references('id')->on('product_units')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            //
        });
    }
};
