<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashier_settings', function (Blueprint $table) {
            $table->dropColumn('use_visa');
        });
    }

    public function down(): void
    {
        Schema::table('cashier_settings', function (Blueprint $table) {
            $table->boolean('use_visa')->default(false); // Re-add the column if the migration is rolled back
        });
    }
};
