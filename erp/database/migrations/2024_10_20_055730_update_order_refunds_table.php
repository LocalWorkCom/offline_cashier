<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrderRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_refunds', function (Blueprint $table) {
            // Drop the foreign key constraint and the old column
            $table->dropForeign(['order_detail_id']);
            $table->dropColumn('order_detail_id');

            // Add the new columns
            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

            $table->unsignedBigInteger('item_id');
            $table->enum('item_type', ['product', 'addon', 'dish']);
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_refunds', function (Blueprint $table) {
            // Drop the new columns
            $table->dropForeign(['order_id']);
            $table->dropColumn(['order_id', 'item_id', 'item_type', 'quantity', 'price']);

            // Re-add the old column
            $table->unsignedBigInteger('order_detail_id')->nullable();
            $table->foreign('order_detail_id')->references('id')->on('order_details')->onDelete('cascade');
        });
    }
}
