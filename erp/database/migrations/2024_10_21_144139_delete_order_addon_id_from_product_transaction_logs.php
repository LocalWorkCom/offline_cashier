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
            /*$table->dropForeign('product_transaction_logs_order_addon_id_foreign');
            $table->dropColumn('order_addon_id');*/
            $table->dropColumn('order_details_id');
            $table->integer('transaction_type')->nullable()->default(1)->comment('1 order, 2 refund order, 3 purchase, 4 refund purchase');
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
