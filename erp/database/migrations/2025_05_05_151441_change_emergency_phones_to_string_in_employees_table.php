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
        Schema::table('employee_contact_infos', function (Blueprint $table) {
            $table->string('emergency_contact_one_phone')->nullable()->change();
            $table->string('emergency_contact_two_phone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_contact_infos', function (Blueprint $table) {
            $table->integer('emergency_contact_one_phone')->nullable()->change();
            $table->integer('emergency_contact_two_phone')->nullable()->change();
        });
    }
};
