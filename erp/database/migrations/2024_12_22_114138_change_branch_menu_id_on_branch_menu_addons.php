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
        Schema::table('branch_menu_addons', function (Blueprint $table) {
            $table->dropForeign(['branch_menu_id']);
            $table->dropColumn('branch_menu_id');

            $table->dropForeign(['addon_category_id']);
            $table->dropColumn('addon_category_id');

            $table->dropColumn('quantity');
            $table->dropColumn('min_addons');
            $table->dropColumn('max_addons');

            $table->unsignedBigInteger('dish_id')->nullable()->after('dish_addon_id');
            $table->foreign('dish_id')->references('id')->on('dishes')->onUpdate('cascade');

            $table->unsignedBigInteger('branch_menu_addon_category_id')->nullable()->after('dish_id');
            $table->foreign('branch_menu_addon_category_id')->references('id')->on('branch_menu_addon_categories')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_menu_addons', function (Blueprint $table) {
            //
        });
    }
};
