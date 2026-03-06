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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('facility_id')->nullable();
            $table->integer('journal_entry_numner')->default(0)->nullable();
            $table->integer('ledger_number')->default(0)->nullable();
            $table->enum('account_type', ['normal','customer','supplier'])->default('normal')->nullable();
            $table->date('date')->nullable();
            $table->text('description')->nullable();
            $table->string('file')->nullable();
            $table->string('repeated')->nullable();
            $table->boolean('is_repeated')->default(1);
            $table->integer('repeated_count')->default(0)->nullable();
            $table->enum('repeated_type', ['day','month'])->default('day')->nullable();
            $table->enum('status', ['draft', 'posted'])->default('draft')->nullable();
            $table->boolean('is_active')->default(1);
            $table->integer('created_by')->nullable();
            $table->integer('modified_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->string('modified_by_type')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade');
            $table->foreign('facility_id')->references('id')->on('facilities')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
