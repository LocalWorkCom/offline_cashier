<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Create new table to store invoice counts with user tracking
        Schema::create('payment_policy_invoice_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_policy_id')->constrained('payment_policies');
            $table->integer('invoice_count')->default(0);
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            
            // Add index for better performance
            $table->index('payment_policy_id');
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // 1. Add the column back
        Schema::table('payment_policies', function (Blueprint $table) {
            $table->integer('invoice_number')->default(0)->after('order_type');
        });

    }
};