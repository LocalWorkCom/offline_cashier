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
        Schema::table('store_transactions', function (Blueprint $table) {
            $table->integer('invoice_id')->default(0)->nullable()->comment('order id, purchase id'); //order_id, purchase_id
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->foreign('branch_id')->references('id')->on('branches')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_transactions', function (Blueprint $table) {
            //
        });
    }
};
