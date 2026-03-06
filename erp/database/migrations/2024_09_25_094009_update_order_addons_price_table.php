<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return  new class  extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_addons', function (Blueprint $table) {
            // Add the deleted_at column for soft deletes
           $table->float('price_before_tax');
           $table->float('price_after_tax');
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
            // Remove the deleted_at column
        });
    }
};
