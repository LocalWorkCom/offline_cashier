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
        Schema::table('table_reservations', function (Blueprint $table) {
            $table->dropForeign('table_reservations_created_by_foreign');
            $table->dropColumn('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('table_reservations', function (Blueprint $table) {
            //
        });
    }
};
