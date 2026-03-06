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
            $table->dropForeign(['recipe_id']); // Drop foreign key constraint first
            $table->dropColumn('recipe_id'); // Then drop the column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->unsignedBigInteger('recipe_id')->nullable()->after('id');
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade');
        });
    }
};
