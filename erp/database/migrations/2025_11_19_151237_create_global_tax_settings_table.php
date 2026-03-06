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
        Schema::create('global_tax_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('percentage', 5, 2);
            $table->dateTime('effective_date');
            $table->foreignId('created_by')->constrained('employees');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_tax_settings');
    }
};
