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
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->string('ids_of_table')->nullable();
            $table->string('name_of_table')->nullable();
            $table->string('value')->nullable();
            $table->boolean('required')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn(['ids_of_table', 'name_of_table', 'value', 'required']);
        });
    }
};
