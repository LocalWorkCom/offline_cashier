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
            // Drop the old enum column
            $table->dropColumn('status');

            // Add the boolean column
            $table->boolean('not_on_time')
                ->default(false) // false = on time, true = not on time
            ; // adjust position if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Remove the boolean column
            $table->dropColumn('not_on_time');

            // Restore the old enum column
            $table->enum('status', ['early', 'late', 'on_time'])
                ->nullable()
                ->after('clock_out_time');
        });
    }
};
