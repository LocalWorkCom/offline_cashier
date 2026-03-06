<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropProductIdAndUnitIdFromOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['unit_id']);

            $table->dropColumn(['product_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            
            // Optionally, re-establish foreign key constraints if they existed
            // $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            // $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });
    }
}
