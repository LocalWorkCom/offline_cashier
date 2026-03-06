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
        Schema::create('cash_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_cash', 15, 2)->default(1000);
            $table->decimal('max_cash', 15, 2)->default(1500);
            $table->boolean('enforce_limit')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnUpdate();
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
        Schema::dropIfExists('cash_payment_settings');
    }
};
