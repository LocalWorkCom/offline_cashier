<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First drop the existing table
        Schema::dropIfExists('inventory_settings');
        
        // Then create the new table with correct structure
        Schema::create('inventory_settings', function (Blueprint $table) {
            $table->id();
            
            $table->boolean('notify_before_expiration')->default(0);
            
            $table->boolean('auto_purchase_order_on_low_stock')->default(0);
            
            $table->enum('notification_frequency', ['daily', 'monthly'])->default('monthly');
            
            $table->integer('no_of_days')->nullable();
            
            $table->boolean('receive_notifications')->default(0);
            
            // Warehouse Staff or Branch Managers
            $table->enum('notification_recipient', ['warehouse_staff', 'branch_managers'])->nullable();
            
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_settings');
    }
};