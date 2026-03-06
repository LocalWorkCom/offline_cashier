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
            $table->unsignedBigInteger('cuisine_id')->nullable()->after('category_id'); // Add cuisine_id column
            $table->foreign('cuisine_id')->references('id')->on('cuisines')->onDelete('set null'); // Add foreign key
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropForeign(['cuisine_id']);
            $table->dropColumn('cuisine_id');
        });
    }
};