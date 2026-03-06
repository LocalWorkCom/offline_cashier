<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class ChangeDishAddonIdToNotNullInOrderAddons extends Migration
{
    public function up()
    {
        Schema::table('order_addons', function (Blueprint $table) {
            // First drop the foreign key constraint
            $table->dropForeign(['dish_addon_id']);
        });
        Schema::table('order_addons', function (Blueprint $table) {
            // Then alter the column
            $table->unsignedBigInteger('dish_addon_id')->nullable(false)->change();
        });
        Schema::table('order_addons', function (Blueprint $table) {
            // Re-add the foreign key constraint
            $table->foreign('dish_addon_id')->references('id')->on('dish_addons')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::table('order_addons', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['dish_addon_id']);
        });
        Schema::table('order_addons', function (Blueprint $table) {
            // Make it nullable again
            $table->unsignedBigInteger('dish_addon_id')->nullable()->change();
        });
        Schema::table('order_addons', function (Blueprint $table) {
            // Re-add the foreign key
            $table->foreign('dish_addon_id')->references('id')->on('dish_addons')->onDelete('cascade');
        });
    }
}