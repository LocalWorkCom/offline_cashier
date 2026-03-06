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
        Schema::table('recipes', function (Blueprint $table) {
            // Remove unnecessary columns
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            
            $table->dropForeign(['cuisine_id']);
            $table->dropColumn('cuisine_id');

            // Add type column (1 for recipe, 2 for addon)
            // Add type column with comment (1 for recipe, 2 for addon)
            $table->tinyInteger('type')
                ->default(1)
                ->after('description_ar')
                ->comment('1: Recipe, 2: Addon');
               });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            // Re-add the removed columns
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('cuisine_id')->nullable();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('cuisine_id')->references('id')->on('cuisines')->onDelete('set null');

            // Remove the type column
            $table->dropColumn('type');
        });
    }
};
