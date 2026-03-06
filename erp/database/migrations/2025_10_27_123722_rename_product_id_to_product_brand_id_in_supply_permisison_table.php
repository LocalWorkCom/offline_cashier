<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('direct_supply_permission_items', function (Blueprint $table) {
            // 🔹 Drop existing foreign key (if exists)
            $table->dropForeign(['item_id']);

            // 🔹 Rename column
            $table->renameColumn('item_id', 'product_brand_id');
        });

        Schema::table('direct_supply_permission_items', function (Blueprint $table) {
            // 🔹 Add new foreign key to product_brands
            $table->foreign('product_brand_id')
                ->references('id')
                ->on('product_brands');
        });
    }

    public function down(): void
    {
        Schema::table('direct_supply_permission_items', function (Blueprint $table) {
            // 🔹 Drop new foreign key
            $table->dropForeign(['product_brand_id']);

            // 🔹 Rename column back
            $table->renameColumn('product_brand_id', 'item_id');
        });

        Schema::table('direct_supply_permission_items', function (Blueprint $table) {
            // 🔹 Restore old relation to products
            $table->foreign('item_id')
                ->references('id')
                ->on('products');
        });
    }
};
