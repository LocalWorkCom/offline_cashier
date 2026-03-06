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
        Schema::table('recipe_addons', function (Blueprint $table) {
            // Drop the foreign key and the addon_id column
            $table->dropForeign(['addon_id']);
            $table->dropColumn('addon_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_addons', function (Blueprint $table) {
            // Re-add the addon_id column and foreign key
            $table->unsignedBigInteger('addon_id')->nullable();
            $table->foreign('addon_id')->references('id')->on('addons')->onDelete('cascade');
        }); 
    }
};
