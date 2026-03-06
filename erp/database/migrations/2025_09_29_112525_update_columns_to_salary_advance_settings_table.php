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
        Schema::table('salary_advance_settings', function (Blueprint $table) {
            $table->dropColumn('max_advance_limit');
            $table->enum('type', ['specific_date', 'month'])->after('percentage_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_advance_settings', function (Blueprint $table) {
            //
        });
    }
};
