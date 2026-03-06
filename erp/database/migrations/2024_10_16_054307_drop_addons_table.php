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
        Schema::dropIfExists('addons');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, you can recreate the addons table structure here if you need a rollback option
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('name_en')->nullable();
            $table->string('name_ar');
            $table->boolean('is_active')->default(true);
            $table->string('image_path')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
