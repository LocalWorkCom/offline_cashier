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
        Schema::create('document_name_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();   // PR, PO, SO, INV, PD
            $table->string('name_ar');
            $table->string('name_en');
            $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_name_types');
    }
};
