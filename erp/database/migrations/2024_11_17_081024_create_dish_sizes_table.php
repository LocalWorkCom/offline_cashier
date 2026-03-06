<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dish_sizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dish_id'); 
            $table->string('size_name_en')->nullable(); 
            $table->string('size_name_ar')->nullable(); 
            $table->decimal('price', 8, 2); 
            $table->timestamps();
            $table->softDeletes();

            // Foreign Key
            $table->foreign('dish_id')->references('id')->on('dishes')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('dish_sizes');
    }
};