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
        Schema::table('product_brands', function (Blueprint $table) {
            // Add ID column if missing
            $table->unsignedBigInteger('id_auto');
            $table->string('sku')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_brands', function (Blueprint $table) {
            //
        });
    }
};
