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
        Schema::create('employee_contact_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnUpdate();
            $table->string('facebook_link')->nullable();
            $table->string('instagram_link')->nullable();
            $table->string('twitter_link')->nullable();
            $table->integer('whatsapp_number')->nullable();
            $table->text('current_address')->nullable();
            $table->text('home_country_address')->nullable();
            $table->string('emergency_contact_one_name')->nullable();
            $table->string('emergency_contact_one_relation')->nullable();
            $table->integer('emergency_contact_one_phone')->nullable();
            $table->string('emergency_contact_two_name')->nullable();
            $table->string('emergency_contact_two_relation')->nullable();
            $table->integer('emergency_contact_two_phone')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('modified_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_contact_infos');
    }
};
