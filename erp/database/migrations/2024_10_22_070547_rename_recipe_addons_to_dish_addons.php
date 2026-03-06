<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('recipe_addons', 'dish_addons');

        Schema::table('dish_addons', function (Blueprint $table) {
            $table->dropForeign('recipe_addons_recipe_id_foreign');

            $table->dropColumn('recipe_id');

            $table->unsignedBigInteger('dish_id')->after('id');
            $table->foreign('dish_id')->references('id')->on('dishes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dish_addons', function (Blueprint $table) {
            $table->dropForeign(['dish_id']);
            $table->dropColumn('dish_id');

            $table->unsignedBigInteger('recipe_id');
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade');
        });

        Schema::rename('dish_addons', 'recipe_addons');
    }
};

