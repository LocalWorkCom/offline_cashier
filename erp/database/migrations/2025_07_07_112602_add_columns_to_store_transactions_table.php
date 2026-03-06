<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToStoreTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table('store_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_order_items_id')->nullable()->after('id');
            $table->unsignedBigInteger('product_stores_id')->nullable()->after('purchase_order_items_id');
            $table->string('sku')->nullable()->after('product_stores_id');
            $table->date('production_date')->require()->after('sku');
            $table->date('expired_date')->nullable()->after('production_date');
            $table->integer('validity_period_days')->nullable()->after('expired_date');

            $table->foreign('purchase_order_items_id')->references('id')->on('purchase_order_items')->onDelete('set null');
            $table->foreign('product_stores_id')->references('id')->on('product_stores')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('store_transactions', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_items_id']);
            $table->dropForeign(['product_stores_id']);
            $table->dropColumn([
                'purchase_order_items_id',
                'product_stores_id',
                'sku',
                'production_date',
                'expired_date',
                'validity_period_days'
            ]);
        });
    }
}
