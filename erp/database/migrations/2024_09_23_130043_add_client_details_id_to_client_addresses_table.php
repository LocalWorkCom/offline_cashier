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
            $table->unsignedBigInteger('client_details_id')->nullable();
            $table->foreign('client_details_id')->references('id')->on('client_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_addresses', function (Blueprint $table) {
            $table->dropForeign(['client_details_id']);
            $table->dropColumn('client_details_id');
        });
    }
};
