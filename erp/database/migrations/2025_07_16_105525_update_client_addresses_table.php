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
            // Drop unnecessary columns
            $table->dropColumn(['city', 'state', 'postal_code']);

            // Make columns nullable
            $table->string('building')->nullable()->change();
            $table->string('address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
Schema::table('client_addresses', function (Blueprint $table) {
            // Re-add dropped columns
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();

            // Revert nullable changes
            $table->string('building')->nullable(false)->change();
            $table->string('address')->nullable(false)->change();
        });    }
};
