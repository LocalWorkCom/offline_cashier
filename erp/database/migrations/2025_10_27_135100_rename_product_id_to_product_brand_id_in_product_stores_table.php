<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('product_stores', function (Blueprint $table) {
            // Drop old foreign key constraint (if exists)
            $table->dropForeign(['product_id']);

            // Rename the column
            $table->renameColumn('product_id', 'product_brand_id');
        });

        Schema::table('product_stores', function (Blueprint $table) {
            // Add new foreign key relation
            $table->foreign('product_brand_id')
                ->references('id')
                ->on('product_brands')
            ;
        });
    }

    public function down()
    {
        Schema::table('product_stores', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['product_brand_id']);

            // Rename back to product_id
            $table->renameColumn('product_brand_id', 'product_id');
        });

        Schema::table('product_stores', function (Blueprint $table) {
            // Restore old relation
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
            ;
        });
    }
};
