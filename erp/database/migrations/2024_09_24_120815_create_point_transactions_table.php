<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->enum('type', ['earn', 'redeem']);
            $table->integer('points');
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamp('transaction_date')->useCurrent();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('modified_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('point_transactions');
    }
};
