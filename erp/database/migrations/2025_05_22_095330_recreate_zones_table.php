<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateZonesTable extends Migration
{
    public function up()
    {
        // Disable foreign key constraints to avoid issues with dependent tables
        Schema::disableForeignKeyConstraints();

        // Drop zones table
        Schema::dropIfExists('zones');

        // Recreate zones table
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->foreignId('storage_location_id')->constrained('storage_locations')->onDelete('restrict');
            $table->timestamps();
        });

        // Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        // Disable foreign key constraints
        Schema::disableForeignKeyConstraints();

        // Drop zones table
        Schema::dropIfExists('zones');

        // Recreate zones table without storage_location_id (for rollback)
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }
}
