<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_addons', function (Blueprint $table) {
            $table->dropForeign('order_addons_recipe_addon_id_foreign');
            $table->dropColumn('recipe_addon_id');
            $table->unsignedBigInteger('dish_addon_id')->nullable();

            $table->foreign('dish_addon_id')->references('id')->on('dish_addons')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_addons', function (Blueprint $table) {
            //
        });
    }
};
