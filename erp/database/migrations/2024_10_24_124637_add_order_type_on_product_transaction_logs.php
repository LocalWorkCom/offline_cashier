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
        Schema::table('product_transaction_logs', function (Blueprint $table) {
            $table->integer('order_type')->nullable()->default(1)->comment('1 product, 2 dish, 3 addon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_transaction_logs', function (Blueprint $table) {
            //
        });
    }
};
