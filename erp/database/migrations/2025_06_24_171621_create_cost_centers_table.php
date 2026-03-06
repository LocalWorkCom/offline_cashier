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
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('facility_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['name_ar', 'deleted_at'], 'unique_name_ar_with_soft_delete');
            $table->unique(['name_en', 'deleted_at'], 'unique_name_en_with_soft_delete');

            $table->foreign('facility_id')->references('id')->on('facilities')->onUpdate('cascade');
            $table->foreign('company_id')->references('id')->on('company_profile_settings')->onUpdate('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('employees')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('employees')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('employees')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_centers');
    }
};
