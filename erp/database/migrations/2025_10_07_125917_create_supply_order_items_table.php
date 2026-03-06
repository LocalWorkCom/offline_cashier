<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplyOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::create('supply_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_order_id')->constrained('supply_orders');
            $table->string('item_name');
            $table->integer('quantity');
            $table->foreignId('unit_id')->constrained('units');
            $table->text('notes')->nullable();
               $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('updated_by_type')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('supply_order_items');
    }
}
