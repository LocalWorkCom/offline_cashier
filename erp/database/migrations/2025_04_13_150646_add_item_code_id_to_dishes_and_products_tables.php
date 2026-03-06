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
        Schema::table('dishes', function (Blueprint $table) {
            $table->unsignedBigInteger('item_code_id')->nullable()->after('id');
            $table->foreign('item_code_id')->references('id')->on('item_codes')->onDelete('set null');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('item_code_id')->nullable()->after('id');
            $table->foreign('item_code_id')->references('id')->on('item_codes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            //
        });
    }
};
