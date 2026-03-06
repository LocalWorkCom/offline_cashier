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
        Schema::create('document_format_signeters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('document_format_id')
                ->constrained('document_formats')
                ->onDelete('cascade');

            $table->string('name_en');
            $table->string('name_ar');

            $table->integer('position')->default(1); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_format_signeters');
    }
};
