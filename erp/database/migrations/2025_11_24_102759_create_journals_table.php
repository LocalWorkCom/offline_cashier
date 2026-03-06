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
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('facility_id')->nullable();
            $table->enum('type', ['assets','liabilities','equity','revenue','expense'])->default('assets')->nullable();
            $table->enum('account_type', ['credit', 'debit'])->default('credit')->nullable();
            $table->integer('level')->default(1)->nullable();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(1);
            $table->integer('created_by')->nullable();
            $table->integer('modified_by')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->string('created_by_type')->nullable();
            $table->string('modified_by_type')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('journals')->onUpdate('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade');
            $table->foreign('facility_id')->references('id')->on('facilities')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
