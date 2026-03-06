<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::table('ingredients', function (Blueprint $table) {
        //     $table->foreign('product_brand_id')->references('id')->on('product_brands')->onDelete('cascade');
        //     $table->dropColumn('unit_id');
        // });
        
        // 1️ Make sure the column can accept NULL
        Schema::table('ingredients', function ($table) {
            $table->unsignedBigInteger('product_brand_id')->nullable()->change();
        });

        // 2️ Proper cleanup of invalid references (JOIN is safe)
        DB::statement("
            UPDATE ingredients AS i
            LEFT JOIN product_brands AS pb ON i.product_brand_id = pb.id
            SET i.product_brand_id = NULL
            WHERE pb.id IS NULL
        ");

        // 3 Finally add the foreign key constraint
        Schema::table('ingredients', function ($table) {
            $table->foreign('product_brand_id')
                  ->references('id')
                  ->on('product_brands')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            //
        });
    }
};
