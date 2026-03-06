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
        Schema::table('colors', function (Blueprint $table) {
            $table->enum('created_type', ['employee', 'admin'])->default('admin')->nullable();
            $table->enum('deleted_type', ['employee', 'admin'])->default('admin')->nullable();
            $table->enum('modified_type', ['employee', 'admin'])->default('admin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colors', function (Blueprint $table) {
            //
        });
    }
};
