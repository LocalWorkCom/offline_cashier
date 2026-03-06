<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old table if it exists
        Schema::dropIfExists('product_transaction_logs');

        // Create it from scratch
        Schema::create('product_transaction_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_transaction_id')
                ->constrained('product_transactions')
            ;

            $table->unsignedBigInteger('model_id')->nullable();
            $table->enum('model_name', [
                'waste_report_items',
                'audit_items',
                'product_opening_balance',
                'purchase_request_items',
                'supply_order_items',
                'direct_supply_permission_items',
                'dsp_item_issues'
            ]);

            // Foreign keys to employees
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('employees')
            ;

            $table->foreignId('modified_by')
                ->nullable()
                ->constrained('employees')
            ;

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_transactions_log');
    }
};
