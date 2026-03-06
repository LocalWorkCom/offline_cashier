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
        Schema::dropIfExists('facility_company_logs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_company_logs', function (Blueprint $table) {
            //
        });
    }
};
