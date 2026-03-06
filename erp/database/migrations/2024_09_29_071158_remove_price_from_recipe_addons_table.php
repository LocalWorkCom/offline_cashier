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
        Schema::table('recipe_addons', function (Blueprint $table) {
            if (Schema::hasColumn('recipe_addons', 'price')) {
                $table->dropColumn('price'); // Remove the price column
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_addons', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->nullable(); // Add the price column back in reverse
        });
    }
};
