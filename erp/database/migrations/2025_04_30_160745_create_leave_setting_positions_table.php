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
        Schema::create('leave_setting_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_setting_id')->nullable();
            $table->unsignedBigInteger('position_id')->nullable();
            $table->integer('day_count')->nullable()->default(0);
            $table->enum('leave_pattern', ['consecutive', 'split'])->default('consecutive');
            $table->integer('split_count')->nullable()->default(0);
            $table->enum('hr_approve', ['yes', 'no'])->default('yes');
            $table->json('higher_position_approve')->nullable();
            $table->enum('higher_position_setting', ['all', 'one'])->default('all');
            $table->json('roles_assign')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('leave_setting_id')->references('id')->on('leave_settings')->onUpdate('cascade');
            $table->foreign('position_id')->references('id')->on('positions')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_setting_positions');
    }
};
