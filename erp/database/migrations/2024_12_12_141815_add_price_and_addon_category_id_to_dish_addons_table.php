<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceAndAddonCategoryIdToDishAddonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dish_addons', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('quantity'); 
            $table->unsignedBigInteger('addon_category_id')->nullable()->after('price');

            // Add foreign key constraint
            $table->foreign('addon_category_id')
                ->references('id')
                ->on('addon_categories')
                ->onDelete('set null'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dish_addons', function (Blueprint $table) {
            $table->dropForeign(['addon_category_id']);
            $table->dropColumn(['price', 'addon_category_id']);
        });
    }
}
