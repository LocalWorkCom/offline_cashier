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
        Schema::table('purchasing_budget_logs', function (Blueprint $table) {
            $table->boolean('prch_manager_notify')->default(false)->after('reference');
            $table->string('reasone')->nullable()->after('prch_manager_notify');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
