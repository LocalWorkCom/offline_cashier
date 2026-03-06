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
        Schema::table('branches', function (Blueprint $table) {
            $table->string('governate')->nullable();
            $table->string('regionCity')->nullable();
            $table->string('street')->nullable();
            $table->string('buildingNumber')->nullable();
            $table->string('postalCode')->nullable();
            $table->string('floor')->nullable();
            $table->string('room')->nullable();
            $table->string('landmark')->nullable();
            $table->string('additionalInformation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            //
        });
    }
};
