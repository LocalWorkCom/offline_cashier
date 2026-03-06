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
        Schema::table('branch_safe', function (Blueprint $table) {
            $table->decimal('deficit_cash', 10, 2)->nullable()->change();
            $table->decimal('deficit_visa', 10, 2)->nullable()->change();
            $table->decimal('cash_amount', 10, 2)->nullable()->change();
            $table->decimal('visa_amount', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_safe', function (Blueprint $table) {
            //
        });
    }
};
