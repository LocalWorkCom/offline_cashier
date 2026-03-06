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
        Schema::create('order_requests', function (Blueprint $table) {
            $table->id();
            $table->enum('request_type', ['merge', 'split', 'print'])->default('split');

            $table->unsignedBigInteger('source_order_id')->nullable()->index();
            $table->unsignedBigInteger('target_order_id')->nullable()->index();

            $table->unsignedBigInteger('requested_by_id');
            $table->enum('requested_by_role', ['waiter', 'cashier']);

            $table->unsignedBigInteger('branch_id');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();
        });
          Schema::create('order_request_split_items', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('order_request_id')->index();
            $table->unsignedBigInteger('order_item_id');
            
            $table->unsignedBigInteger('from_order_id');
            $table->unsignedBigInteger('to_order_id');
            
            $table->integer('quantity');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
