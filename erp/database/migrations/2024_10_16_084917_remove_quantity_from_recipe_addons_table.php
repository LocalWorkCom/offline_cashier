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
            // Drop the quantity column
            $table->dropColumn('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_addons', function (Blueprint $table) {
            // Add the quantity column back
            $table->decimal('quantity', 8, 2)->after('product_unit_id');
        });
    }
};
