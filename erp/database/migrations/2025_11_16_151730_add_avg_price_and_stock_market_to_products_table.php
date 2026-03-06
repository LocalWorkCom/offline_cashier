<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_brands', function (Blueprint $table) {
            $table->decimal('avg_price', 10, 2)->nullable();
            $table->boolean('stock_market')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('product_brands', function (Blueprint $table) {
            $table->dropColumn(['avg_price', 'stock_market']);
        });
    }
};
