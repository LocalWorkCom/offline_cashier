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
        Schema::table('order_trackings', function (Blueprint $table) {
            $table->enum('order_status', ['pending', 'in_progress', 'completed', 'on_way', 'delivered', 'readyForPickup', 'cancelled'])
                ->default('pending')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_trackings', function (Blueprint $table) {
            $table->enum('order_status', ['pending', 'in_progress', 'completed', 'on_way', 'delivered', 'cancelled'])
                ->default('pending')
                ->change();
        });
    }
};
