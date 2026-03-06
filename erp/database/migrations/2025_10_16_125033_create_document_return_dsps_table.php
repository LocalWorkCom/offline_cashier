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
        Schema::create('document_return_dsps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_dsp_id');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_return_dsps');
    }
};
