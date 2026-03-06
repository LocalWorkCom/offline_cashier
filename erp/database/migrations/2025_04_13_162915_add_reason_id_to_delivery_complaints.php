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
        Schema::table('delivery_complaints', function (Blueprint $table) {
            $table->foreignId('reason_id')->nullable()->constrained('delivery_cancellation_reasons')->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_complaints', function (Blueprint $table) {
            //
        });
    }
};
