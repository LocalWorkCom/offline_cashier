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
         Schema::create('purchase_settings', function (Blueprint $table) {
            $table->id();

            $table->boolean('auto_creation_po')->default(false);
            $table->boolean('notify_PM')->default(false)->comment('Allow notification for Procurement Manager');     // Procurement Manager
            $table->boolean('notify_EMPO')->default(false)->comment('Allow notification for Employee assigned Purchase order');   // Employee Purchase Officer

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('employees')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_settings');
    }
};
