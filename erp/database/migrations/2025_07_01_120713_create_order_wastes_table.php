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
        Schema::create('order_wastes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable();
            $table->foreignId('order_detail_id')->nullable();
            $table->foreignId('order_addon_id')->nullable();
            $table->integer('original_quantity')->nullable();
            $table->integer('waste_quantity')->nullable();
            $table->foreignId('waste_reason_id')->nullable();
            $table->enum('type', ['temporary', 'permanent'])->default('temporary');
            $table->enum('flag', ['dish', 'addon'])->nullable();
            $table->tinyInteger('reused')->default(0);
            $table->text('note')->nullable();
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
        Schema::dropIfExists('order_wastes');
    }
};
