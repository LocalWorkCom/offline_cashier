<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateCustomersWalletTable extends Migration
{
    public function up()
    {
        Schema::create('customers_wallet', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('order_transaction_id')->nullable();
            $table->decimal('withdraw', 10, 2)->default(0);
            $table->enum('withdraw_type', ['cash', 'refund']);
            $table->dateTime('withdraw_date')->nullable();
            $table->dateTime('date')->nullable();
            $table->unsignedBigInteger('withdrawed_by')->nullable(); // could be staff/admin
            $table->timestamps();

            // Add foreign keys if needed:
            // $table->foreign('branch_id')->references('id')->on('branches');
            // $table->foreign('client_id')->references('id')->on('clients');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers_wallet');
    }
}
