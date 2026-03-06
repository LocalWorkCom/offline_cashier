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
        Schema::table('currency_exchanges', function (Blueprint $table) {
            $table->unsignedBigInteger('facility_id')->nullable()->after('exchange_currency_id');
            $table->foreign('facility_id')->references('id')->on('facilities')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currency_exchanges', function (Blueprint $table) {
            //
        });
    }
};
