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
        Schema::create('inventory_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('expiry_alert');
            $table->boolean('dish_blocking_status')->default(false);
            $table->boolean('alert_recipients_status')->default(false);
            $table->boolean('auto_replenishment_status')->default(false);
            $table->boolean('branch_automatic_stock_requests')->default(false);
            $table->boolean('branch_manual_adjustments')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_settings');
    }
};
