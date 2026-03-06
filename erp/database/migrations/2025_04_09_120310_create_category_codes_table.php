<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('category_codes', function (Blueprint $table) {
            $table->id();
            $table->string('item_code');
            $table->string('code_name');
            $table->string('code_name_ar');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('category')->nullable();
            $table->string('activity')->nullable();
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_codes');
    }
};
