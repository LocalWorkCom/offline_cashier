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
        Schema::create('absence_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('penalty_mode', ['one_day', 'two_day', 'manual'])->default('one_day');
            $table->enum('deduct_from', ['basic', 'total'])->default('basic');
            $table->boolean('allow_manual_override')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_settings');
    }
};
