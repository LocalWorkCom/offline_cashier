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
           // Remove from orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_number_code', 'whatsapp_number']);
        });

        // Add to client_addresses table
        Schema::table('client_addresses', function (Blueprint $table) {
            $table->string('whatsapp_number_code')->nullable()->after('address_phone'); // Adjust position as needed
            $table->string('whatsapp_number')->nullable()->after('whatsapp_number_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_addresses', function (Blueprint $table) {
            //
        });
    }
};
