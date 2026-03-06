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
        Schema::create('journal_entry_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->unsignedBigInteger('cost_center_id')->nullable();
            $table->integer('credit')->default(0)->nullable();
            $table->integer('debit')->default(0)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(1);
            $table->integer('created_by')->nullable();
            $table->integer('modified_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->string('modified_by_type')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->onUpdate('cascade');
            $table->foreign('cost_center_id')->references('id')->on('cost_centers')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entry_details');
    }
};
