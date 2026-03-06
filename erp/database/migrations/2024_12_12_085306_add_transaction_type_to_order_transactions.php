<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransactionTypeToOrderTransactions extends Migration
{
    public function up()
    {
        Schema::table('order_transactions', function (Blueprint $table) {
            $table->boolean('is_refund')->default(false);
            $table->dropColumn('order_type');
        });
    }

    public function down()
    {
        Schema::table('order_transactions', function (Blueprint $table) {
            $table->dropColumn('is_refund');
        });
    }
}
