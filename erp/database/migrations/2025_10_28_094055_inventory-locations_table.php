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
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->id();
            
            // Name fields
            $table->string('name_ar');
            $table->string('name_en');
            
            // Creator information
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_type')->nullable();
            
            // Modifier information
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->string('modified_by_type')->nullable();
            
            // Soft delete information
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->string('deleted_by_type')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['name_ar', 'name_en']);
            $table->index('created_by');
            $table->index('modified_by');
            $table->index('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_locations');
    }
};