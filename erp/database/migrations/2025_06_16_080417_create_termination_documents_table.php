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
        //test
        Schema::create('termination_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('termination_id');
            $table->foreign('termination_id')->references('id')->on('termination_of_services')->onDelete('cascade');
            $table->string('file');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('termination_documents');
    }
};
