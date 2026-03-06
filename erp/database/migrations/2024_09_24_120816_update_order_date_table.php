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
        Schema::table('orders', function (Blueprint $table) {
            // Add the deleted_at column for soft deletes
            $table->date('date'); // Place this column after the 'updated_at' column
            $table->unsignedBigInteger('table_id')->nullable()->change();
            $table->unsignedBigInteger('discount_id')->nullable()->change();
            $table->unsignedBigInteger('coupon_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Remove the deleted_at column
        });
    }
};
