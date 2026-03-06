<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('daily_excuse_hours', 5, 2)->default(0)->after('salary'); 
            $table->decimal('monthly_excuse_hours', 5, 2)->default(0)->after('daily_excuse_hours'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['daily_excuse_hours', 'monthly_excuse_hours']);
        });
    }
};
