<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOrderTransactionEnum extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE order_transactions MODIFY COLUMN payment_method ENUM('cash', 'credit', 'online', 'credit_with_delivery') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE order_transactions MODIFY COLUMN payment_method ENUM('cash', 'credit', 'online') NOT NULL");
    }
}
