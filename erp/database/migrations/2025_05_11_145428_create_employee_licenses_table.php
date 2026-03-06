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
        Schema::create('employee_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');

            // General License Info
            $table->boolean('has_license')->default(false);
            $table->string('license_country')->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->string('license_copy')->nullable(); // path to uploaded file

            // Kuwaiti License
            $table->boolean('has_kuwaiti_license')->default(false);
            $table->string('kuwaiti_license_copy')->nullable();
            $table->date('kuwaiti_license_expiry_date')->nullable();

            // Egyptian License
            $table->boolean('has_egyptian_license')->default(false);
            $table->string('egyptian_license_copy')->nullable();
            $table->date('egyptian_license_expiry_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_licenses');
    }
};
