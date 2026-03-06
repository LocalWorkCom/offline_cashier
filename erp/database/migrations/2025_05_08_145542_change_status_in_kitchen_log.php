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
    public function up(): void
    {
        Schema::table('kitchen_logs', function (Blueprint $table) {
            DB::statement("ALTER TABLE kitchen_logs MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'delivered', 'readyForPickup', 'cancelled') DEFAULT 'pending' NULL");
            $table->enum('dish_order', ['-1', '0', '1', '2'])->default(-1)->nullable()->comment('-1: All Dishes at Once, 0: Before Meal, 1: Main Meal, 2: After Meal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kitchen_log', function (Blueprint $table) {
            //
        });
    }
};
