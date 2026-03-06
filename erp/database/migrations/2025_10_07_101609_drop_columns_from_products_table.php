<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'main_unit_id',
                'currency_code',
                'brand_id',
                'sku',
                'is_valid',
                'barcode',
                'is_remind',
                'is_have_expired'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('main_unit_id')->nullable();
            $table->string('currency_code')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('sku')->nullable();
            $table->boolean('is_valid')->default(true);
            $table->string('barcode')->nullable();
            $table->boolean('is_remind')->default(false);
            $table->boolean('is_have_expired')->default(false);
        });
    }
};
