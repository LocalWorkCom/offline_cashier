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
        DB::statement("ALTER TABLE delivery_complaints MODIFY manage ENUM('customer_service', 'admin') DEFAULT 'customer_service'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE delivery_complaints MODIFY manage ENUM('call_center', 'admin') DEFAULT 'call_center'");
    }
};
