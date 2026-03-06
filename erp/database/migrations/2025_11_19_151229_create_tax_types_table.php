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
        Schema::create('tax_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // VAT, Service Tax
            $table->decimal('percentage', 5, 2); // 0.01 to 100
            $table->boolean('is_default')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('employees');
            $table->foreignId('updated_by')->nullable()->constrained('employees');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_types');
    }
};
