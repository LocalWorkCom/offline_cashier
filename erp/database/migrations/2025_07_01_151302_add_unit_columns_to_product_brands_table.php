<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitColumnsToProductBrandsTable extends Migration
{
    public function up(): void
    {
        Schema::table('product_brands', function (Blueprint $table) {
            $table->unsignedBigInteger('default_unit_id')->nullable()
                ->after('expiration_type')
                ->comment('Default unit used for supplier');

            $table->unsignedBigInteger('base_unit_id')->nullable()
                ->after('default_unit_id')
                ->comment('Base unit used for kitchen');

            $table->foreign('default_unit_id')->references('id')->on('units')->nullOnDelete();
            $table->foreign('base_unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_brands', function (Blueprint $table) {
            $table->dropForeign(['default_unit_id']);
            $table->dropForeign(['base_unit_id']);
            $table->dropColumn(['default_unit_id', 'base_unit_id']);
        });
    }
}
