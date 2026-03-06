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
        Schema::create('tips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->enum('payment_method', ['cash', 'visa','mixed'])->default('cash');
            $table->double('payment_amount')->default(0); // remaining_blance
            $table->double('change_amount')->default(0);
            $table->enum('tips_aption', ['tip_the_change', 'tip_specific_amount','no_tip'])->default('no_tip');
            $table->double('tip_amount')->default(0);
            $table->double('bill_amount')->default(0);
            $table->double('tip_specific_amount')->default(0);
            $table->double('total_with_tip')->default(0);
            $table->double('returned_amount')->default(0);
            $table->date('date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tips');
    }
};
