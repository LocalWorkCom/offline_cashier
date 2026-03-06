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
        DB::statement("
            ALTER TABLE pricing_deal_shipments
            MODIFY pricing_basis
            ENUM('per_order', 'per_shipment', 'per_unit_basis', 'free')
            NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_deal_shipments', function (Blueprint $table) {
            //
        });
    }
};
