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
        // Rename foreign keys
        Schema::table('dish_categories', function (Blueprint $table) {
            $table->dropForeign('recipe_categories_parent_id_foreign');
            $table->foreign('parent_id')->references('id')->on('dish_categories')->onDelete('cascade');

            $table->dropForeign('recipe_categories_created_by_foreign');
            $table->foreign('created_by')->references('id')->on('users');

            $table->dropForeign('recipe_categories_modified_by_foreign');
            $table->foreign('modified_by')->references('id')->on('users');

            $table->dropForeign('recipe_categories_deleted_by_foreign');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert foreign key names
        Schema::table('dish_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->foreign('parent_id')->references('id')->on('recipe_categories')->onDelete('cascade');

            $table->dropForeign(['created_by']);
            $table->foreign('created_by')->references('id')->on('users');

            $table->dropForeign(['modified_by']);
            $table->foreign('modified_by')->references('id')->on('users');

            $table->dropForeign(['deleted_by']);
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }
};
