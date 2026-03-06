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
        Schema::create('return_dsps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dsp_id');
            $table->unsignedBigInteger('reason_id');
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->json('items');
            $table->json('quantity');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->string('modified_by_type')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('approved_by_type')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->string('rejected_by_type')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->string('submitted_by_type')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('reason_id')->references('id')->on('reasone_return_dsps')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_dsps');
    }
};
