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
        Schema::table('payment_frequencies', function (Blueprint $table) {
            // For weekly salary system: store which weekday (0=Sunday, 6=Saturday)
            $table->unsignedTinyInteger('payment_weekday')->nullable()
                ->comment('Day of week for weekly payment (0=Sunday, 6=Saturday)');

            // For monthly payment system: store which day of the month (1-31)
            $table->unsignedTinyInteger('payment_monthday')->nullable()
                ->comment('Day of month for monthly payment (1-31)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_frequencies', function (Blueprint $table) {
            $table->dropColumn(['payment_weekday', 'payment_monthday']);
        });
    }
};
