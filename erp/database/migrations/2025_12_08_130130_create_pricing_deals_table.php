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
        Schema::create('pricing_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->enum('deal_type', ['one_time', 'contract']); // 'one_time' or 'contract'
            $table->string('file');

            $table->date('start_date')->default(now());
            $table->date('end_date')->nullable();
            $table->date('period')->nullable();

            $table->decimal('total_before_negotiated', 15, 2)->nullable();
            $table->decimal('total_after_negotiated', 15, 2)->nullable();
            $table->text('delivery_date')->nullable();
            $table->text('payment_terms')->nullable()->comment('شروط الدفع');
            $table->text('penalty_clause')->nullable()->comment('بند الغرامه ');
            $table->text('discount_by_quantity')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('status')->default(1)->comment('[1=>available, 0=>expired]');
            $table->boolean('is_active')->default(1);

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('pricing_deal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_deal_id')->constrained('pricing_deals')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete(); // item description
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();

            $table->integer('quantity')->nullable()->default(1);
            $table->decimal('unit_price', 15, 2)->nullable()->comment('price as 1 of unit');
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('tax', 15, 2)->nullable();
            $table->decimal('net_amount', 15, 2)->nullable()->comment('الاجمالى'); // subtotal
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('pricing_deal_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_deal_id')
                ->constrained('pricing_deals')
                ->cascadeOnDelete();
            $table->foreignId('vendor_id')
                ->constrained('vendors')->nullable()
                ->cascadeOnDelete();
            $table->string('from_address')->nullable();
            $table->string('to_address')->nullable();

            $table->decimal('price', 15, 2)->nullable();

            $table->enum('pricing_basis', [
                'per_order',
                'per_kg',
                'per_package',
                'per_distance'
            ])->nullable();

            $table->text('notes')->nullable();
            $table->string('file')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_deals');
    }
};
