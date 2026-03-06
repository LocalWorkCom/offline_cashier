<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('excuse_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('max_daily_hours')->nullable()->default(4);
            $table->integer('max_monthly_hours')->nullable()->default(12);
            $table->integer('before_request_period')->default(1);
            $table->boolean('is_paid')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excuse_settings');
    }
};
