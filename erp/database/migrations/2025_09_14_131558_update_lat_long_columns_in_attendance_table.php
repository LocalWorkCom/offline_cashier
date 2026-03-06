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
        Schema::table('attendances', function (Blueprint $table) {
            // Rename existing columns
            $table->renameColumn('latitude', 'latitude_in');
            $table->renameColumn('longitude', 'longitude_in');

            // Add new columns for clock out
            $table->decimal('latitude_out', 10, 7)->nullable();
            $table->decimal('longitude_out', 10, 7)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Rollback new columns
            $table->dropColumn(['latitude_out', 'longitude_out']);

            // Rename back
            $table->renameColumn('latitude_in', 'latitude');
            $table->renameColumn('longitude_in', 'longitude');
        });
    }
};
