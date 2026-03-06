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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name_en')->nullable();
            $table->string('name_ar');
            $table->text('address_en')->nullable();
            $table->text('address_ar')->nullable();
            $table->text('latitute')->nullable();
            $table->text('longitute')->nullable();
            $table->unsignedBigInteger('country_id');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('opening_hours')->nullable();
            
            $table->foreign('country_id')->references('id')->on('countries');
            
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
        Schema::dropIfExists('branches');
    }
};
