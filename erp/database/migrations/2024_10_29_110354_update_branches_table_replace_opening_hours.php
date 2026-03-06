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
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('opening_hours');
            $table->time('opening_hour')->nullable()->after('manager_name');
            $table->time('closing_hour')->nullable()->after('opening_hour');
            $table->boolean('is_delivery')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('opening_hours')->nullable()->after('manager_name');
            $table->dropColumn(['opening_hour', 'closing_hour']);
        });
    }
};
