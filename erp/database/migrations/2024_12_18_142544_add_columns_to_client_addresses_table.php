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
        Schema::table('client_addresses', function (Blueprint $table) {
            $table->enum('address_type', ['apartment', 'villa', 'office'])->nullable()->default('apartment');
            $table->string('building')->nullable();
            $table->integer('floor_number')->nullable();
            $table->integer('apartment_number')->nullable();
            $table->string('notes')->nullable();
            $table->string('country_code');
            $table->string('address_phone');
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
