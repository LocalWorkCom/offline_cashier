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
        Schema::create('store_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_transaction_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_unit_id')->nullable();
            $table->unsignedBigInteger('product_size_id')->nullable();
            $table->unsignedBigInteger('product_color_id')->nullable();
            $table->unsignedBigInteger('country_id')->comment('to get currency id');
            $table->date('expirt_date')->nullable();
            $table->double('count', 15,2)->default(0);
            $table->decimal('price', 8,2)->default(0);
            $table->double('total_price', 15,2)->default(0);
            $table->timestamps();

            $table->foreign('store_transaction_id')->references('id')->on('store_transactions')->opUpdate('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onUpdate('cascade');
            $table->foreign('product_unit_id')->references('id')->on('product_units')->onUpdate('cascade');
            $table->foreign('product_size_id')->references('id')->on('product_sizes')->onUpdate('cascade');
            $table->foreign('product_color_id')->references('id')->on('product_colors')->onUpdate('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_transaction_details');
    }
};
