<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add new enum column for make_type
            $table->enum('make_type', ['waiter', 'cashier', 'customer_service', 'app', 'site'])->nullable();

            // Change orderNumber from int to varchar
            $table->string('order_number')->change();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop make_type column
            $table->dropColumn('make_type');

            // Revert orderNumber back to integer
            $table->integer('order_number')->change();
        });
    }
};
