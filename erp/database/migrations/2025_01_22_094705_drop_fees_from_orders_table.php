<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropFeesFromOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_addons', function (Blueprint $table) {
            $table->dropColumn(['total', 'price']); // Drop the columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_addons', function (Blueprint $table) {
            $table->decimal('total', 10, 2)->nullable(); // Re-add the 'total' column
            $table->decimal('price', 10, 2)->nullable(); // Re-add the 'price' column
        });
    }
}
