<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('client_addresses', function (Blueprint $table) {
            $table->string('apartment_number')->nullable()->change();
        });
        DB::statement("ALTER TABLE client_addresses MODIFY address_type ENUM('apartment', 'villa', 'office', 'hotel') NOT NULL");
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
