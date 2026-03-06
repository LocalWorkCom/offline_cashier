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
        Schema::table('payment_policies', function (Blueprint $table) {
            $table->dropColumn('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('payment_policies', function (Blueprint $table) {
            $table->integer('invoice_number')->default(0)->after('order_type');
        });
    }
};