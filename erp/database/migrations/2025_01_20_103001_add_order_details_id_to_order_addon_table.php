<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderDetailsIdToOrderAddonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_addons', function (Blueprint $table) {
            $table->unsignedBigInteger('order_details_id')->nullable()->after('id'); // Adjust the position with 'after'
            $table->foreign('order_details_id')->references('id')->on('order_details')->onDelete('cascade');
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
            $table->dropForeign(['order_details_id']);
            $table->dropColumn('order_details_id');
        });
    }
}
