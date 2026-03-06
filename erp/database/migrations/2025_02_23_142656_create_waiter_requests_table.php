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
    {    Schema::create('waiter_requests', function (Blueprint $table) {
        $table->id(); // Auto-increment primary key
        $table->integer('type')->comment('0: Split, 1: Merge'); // Type of request
        $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete(); // Created by an employee
        $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // User ID
        $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete(); // Order ID
        $table->json('order_ids')->nullable()->comment('JSON array of order IDs'); // JSON field to store multiple order IDs
        $table->integer('status')->default(0)->comment('0: Pending, 1: Approved, 2: Rejected'); // Status
        $table->text('reason')->nullable()->comment('Reason for the request reject'); // Reason
        $table->softDeletes(); // Adds `deleted_at` for soft delete functionality
        $table->timestamps(); // Adds `created_at` and `updated_at`
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waiter_requests');

    }
};
