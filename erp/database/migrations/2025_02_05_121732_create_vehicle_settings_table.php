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
        Schema::create('vehicle_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('vehicle_type', ['car', 'motorcycle']);
            $table->integer('vehicle_max');
            $table->integer('vehicle_min');
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys to the users table
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('modified_by')->nullable()->constrained('users')->cascadeOnUpdate();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_settings');
    }
};

