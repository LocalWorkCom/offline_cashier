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
        Schema::create('job_related_penalty_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_related_id');

            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');

            $table->foreign('job_related_id')->references('id')->on('job_related_penalties')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_related_penalty_documents');
    }
};
