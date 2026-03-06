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
            $table->uuid('country_id')->nullable()->after('id');
            $table->unsignedBigInteger('city_id')->nullable()->after('country_id');
            $table->unsignedBigInteger('area_id')->nullable()->after('city_id');

            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('set null');
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
