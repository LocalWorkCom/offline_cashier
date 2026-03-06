<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('supply_order_items', function (Blueprint $table) {
            // Drop old column if it exists
            if (Schema::hasColumn('supply_order_items', 'item_name')) {
                $table->dropColumn('item_name');
            }

            // Add the new foreign key column
            $table->foreignId('product_brand_id')
                ->after('supply_order_id')
                ->constrained('product_brands')
            ;
        });
    }

    public function down()
    {
        Schema::table('supply_order_items', function (Blueprint $table) {
            // Rollback changes
            $table->dropForeign(['product_brand_id']);
            $table->dropColumn('product_brand_id');

            // Restore old column
            $table->string('item_name')->after('supply_order_id');
        });
    }
};
