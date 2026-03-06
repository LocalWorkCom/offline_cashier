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
        Schema::create('damy_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('brand');
            $table->string('unit');
            $table->decimal('quantity', 15, 2);
            $table->string('note')->nullable();
            $table->boolean('status')->default(1)->comment('1 -> need add , 0 -> added');
            $table->foreignId('pr_id')->constrained('purchase_requests')->onDelete('cascade');
            $table->foreignId('added_live_by')->nullable()->constrained('employees')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damy_products');
    }
};
