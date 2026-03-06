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
        Schema::table('waiter_requests', function (Blueprint $table) {
            $table->json('order_items_ids')->nullable()->comment('JSON array of order items IDs')->after('order_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waiter_requests', function (Blueprint $table) {
            $table->dropColumn('order_items_ids');
        });
    }
};
