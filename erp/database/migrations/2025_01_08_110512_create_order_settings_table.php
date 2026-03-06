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
        Schema::create('order_settings', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('tax_application')->default(0); // 0: tax not included, 1: tax included
            $table->tinyInteger('coupon_application')->default(0); // 0: before tax, 1: after tax
            $table->decimal('tax_percentage', 5, 2)->default(0.00);
            $table->integer('time_cancellation')->nullable();
            $table->integer('delivery_time')->nullable();
            $table->integer('delivery_difference')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('modified_by')->nullable()->constrained('users')->cascadeOnUpdate();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_settings');
    }
};
