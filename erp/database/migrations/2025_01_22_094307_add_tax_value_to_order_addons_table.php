<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxValueToOrderAddonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_addons', function (Blueprint $table) {
            $table->decimal('tax_value', 10, 2)->nullable(); // Adjust 'price' to the column after which you want to place 'tax_value'
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
            $table->dropColumn('tax_value');
        });
    }
}
