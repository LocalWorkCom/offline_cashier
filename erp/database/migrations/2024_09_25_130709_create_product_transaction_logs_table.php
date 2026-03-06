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
        Schema::create('product_transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('product_size_id')->nullable();
            $table->unsignedBigInteger('product_color_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->float('count');
            $table->date('expired_date')->nullable();
            $table->string('model_name')->nullable();
            $table->unsignedBigInteger('created_by')->default(1);
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onUpdate('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onUpdate('cascade');
            $table->foreign('product_size_id')->references('id')->on('product_sizes')->onUpdate('cascade');
            $table->foreign('product_color_id')->references('id')->on('product_colors')->onUpdate('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_transaction_logs');
    }
};
