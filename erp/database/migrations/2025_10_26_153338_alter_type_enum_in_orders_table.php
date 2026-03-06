<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::table('orders', function (Blueprint $table) {
        //     //
        // });
        DB::statement("ALTER TABLE orders MODIFY COLUMN type ENUM('Delivery','CallCenter','Takeaway','Online','dine-in','reservation-table','talabat') DEFAULT 'dine-in'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('orders', function (Blueprint $table) {
        //     //
        // });
    }
};
