<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_brands', function (Blueprint $table) {
            $table->text('note')->nullable()->after('stock_market');
        });
    }

    public function down(): void
    {
        Schema::table('product_brands', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
