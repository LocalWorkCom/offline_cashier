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
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->boolean('full_payment_preparation_setting')->default(0);
            $table->boolean('deposit_preparation_setting')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->dropColumn('full_payment_preparation_setting');
            $table->dropColumn('deposit_preparation_setting');
        });
    }
};
