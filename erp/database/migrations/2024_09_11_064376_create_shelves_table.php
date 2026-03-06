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
        Schema::create('shelves', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('division_id'); 
            $table->string('name_ar'); 
            $table->string('name_en')->nullable(); 
            
            $table->foreign('division_id')->references('id')->on('divisions'); 
            $table->unsignedBigInteger('created_by');  
            $table->foreign('created_by')->references('id')->on('users');
            
            $table->unsignedBigInteger('modified_by')->nullable();  
            $table->foreign('modified_by')->references('id')->on('users');
            
            $table->unsignedBigInteger('deleted_by')->nullable();  
            $table->foreign('deleted_by')->references('id')->on('users');
            
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shelves');
    }
};
