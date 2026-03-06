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
        Schema::create('penalty_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penalty_id');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->foreignId('created_by')->nullable();
            $table->foreignId('modified_by')->nullable();
            $table->foreignId('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalty_documents');
    }
};
