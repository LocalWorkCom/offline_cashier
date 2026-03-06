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
        Schema::table('product_limit', function (Blueprint $table) {
            // Add the deleted_at column for soft deletes
            if (Schema::hasColumn('product_limit', 'store_category_id')) {
                $table->dropForeign(['store_category_id']);
                $table->dropColumn('store_category_id');

            }
            $table->unsignedBigInteger('store_id');
            $table->foreign('store_id')->references('id')->on('stores');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_limit', function (Blueprint $table) {
            // Remove the deleted_at column
        });
    }
};
