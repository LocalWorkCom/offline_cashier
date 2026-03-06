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
            // Add the addon_id column
            $table->unsignedBigInteger('addon_id')->nullable()->after('recipe_id');

            // Set up the foreign key constraint on addon_id
            $table->foreign('addon_id')->references('id')->on('recipes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_addons', function (Blueprint $table) {
            // Drop the foreign key and the addon_id column
            $table->dropForeign(['addon_id']);
            $table->dropColumn('addon_id');
        });
    }
};
