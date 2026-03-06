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
        Schema::create('offer_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers');
            $table->enum('offer_type',['dishes','categories','products']);
            $table->unsignedInteger('type_id');
            $table->integer('count');
            $table->integer('discount')->comment('percentage');
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('modified_by')->nullable()->constrained('users')->cascadeOnUpdate();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_details');
    }
};
