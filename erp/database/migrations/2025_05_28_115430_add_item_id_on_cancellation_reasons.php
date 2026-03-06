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
        Schema::table('cancellation_reasons', function (Blueprint $table) {
            $table->unsignedBigInteger('order_details_id')->nullable()->after('order_id');
            $table->enum('type', ['client', 'admin', 'branch manager','waiter'])->default('admin')->change();
            $table->foreign('order_details_id')->references('id')->on('order_details')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cancellation_reasons', function (Blueprint $table) {
            //
        });
    }
};
