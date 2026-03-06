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
            $table->string('apartment_number')->change();
            $table->string('floor_number')->change();
            $table->string('street')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_addresses', function (Blueprint $table) {
            $table->integer('apartment_number')->change();
            $table->integer('floor_number')->change();
            $table->dropColumn('street');
        });
    }
};
