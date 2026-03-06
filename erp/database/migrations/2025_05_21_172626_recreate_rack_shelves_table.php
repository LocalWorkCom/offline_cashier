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
        // Drop the existing rack_shelves table
        Schema::dropIfExists('rack_shelves');

        // Create the new rack_shelves table
        Schema::create('rack_shelves', function (Blueprint $table) {
            $table->id(); // Primary key: BIGINT UNSIGNED AUTO_INCREMENT
            $table->foreignId('zone_id')->constrained()->onDelete('cascade'); // Foreign key to zones(id), BIGINT UNSIGNED
            $table->string('identifier');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the recreated rack_shelves table
        Schema::dropIfExists('rack_shelves');
    }
};
