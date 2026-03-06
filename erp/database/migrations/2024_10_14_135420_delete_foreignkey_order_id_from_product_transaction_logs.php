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
            $table->dropForeign('product_transaction_logs_order_id_foreign');
            $table->dropForeign('product_transaction_logs_order_details_id_foreign');
            $table->dropColumn('order_id');
            $table->dropColumn('order_details_id');
            $table->integer('order_type')->default(0)->comment('0 for store, 1 for order, 2 for purchase');
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
