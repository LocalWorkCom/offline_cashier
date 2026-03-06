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
                    Schema::table('branch_settings', function (Blueprint $table) {
                        $table->integer('table_reservation_deposit')->default(0);
                        $table->decimal('order_reservation_deposit', 5, 2)->default(0);
                    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            //
        });
    }
};
