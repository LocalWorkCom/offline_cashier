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
        Schema::table('dishes', function (Blueprint $table) {
            // Drop old foreign key
            $table->dropForeign(['category_id']);

            // Add the correct foreign key constraint with `dish_categories`
            $table->foreign('category_id')
                ->references('id')
                ->on('dish_categories')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['category_id']);

            // Re-add the old foreign key constraint with `categories`
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('set null');
        });
    }
};
