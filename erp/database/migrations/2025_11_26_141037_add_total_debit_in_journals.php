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
        Schema::table('journals', function (Blueprint $table) {
            $table->decimal('debit', 15,2)->default(0)->after('code');
            $table->decimal('credit', 15,2)->default(0)->after('debit');
            $table->decimal('balance', 15,2)->default(0)->after('credit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            //
        });
    }
};
