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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');  
            $table->string('name_en')->nullable();  
            $table->string('name_ar');  
            $table->text('description_en')->nullable();  
            $table->text('description_ar')->nullable();  

           
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');  

         
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
        Schema::disableForeignKeyConstraints(); // Disable foreign key checks
    
        Schema::dropIfExists('stores');
    
        Schema::enableForeignKeyConstraints(); // Re-enable foreign key checks
    }
};
