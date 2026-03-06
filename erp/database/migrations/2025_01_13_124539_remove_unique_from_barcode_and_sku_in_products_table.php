<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUniqueFromBarcodeAndSkuInProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_barcode_unique'); // Drop unique constraint on barcode
            $table->dropUnique('products_sku_unique');     // Drop unique constraint on sku
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unique('barcode'); // Restore unique constraint on barcode
            $table->unique('sku');     // Restore unique constraint on sku
        });
    }
};
