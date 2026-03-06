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
        Schema::table('order_transactions', function (Blueprint $table) {
            $table->decimal('paid', 10, 3)->change();
            $table->decimal('refund', 10, 3)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('order_transactions', function (Blueprint $table) {
            $table->double('paid', 8, 2)->change();
            $table->double('refund', 8, 2)->nullable()->change();
        });
    }
};
