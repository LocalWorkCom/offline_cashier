<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonusSettingsTable extends Migration
{
    public function up(): void
    {
        Schema::create('bonus_settings', function (Blueprint $table) {
            $table->id();

            $table->decimal('max_bonus_percentage', 5, 2)->comment('Maximum allowed bonus percentage of monthly salary');

            $table->decimal('fixed_bonus_cap', 10, 2)->comment('Maximum bonus amount allowed per request');

            $table->integer('days_convertible_to_money')->comment('Number of days that can be converted into money');

            // Options: 'monthly_salary', 'specific_date', 'both'
            $table->enum('disbursement_timing', ['monthly_salary', 'specific_date', 'both'])->comment('When the bonus is disbursed');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onUpdate('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_settings');
    }
}
