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
        Schema::table('salary_advance_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('salary_advance_requests', 'advance_date')) {
                $table->date('advance_date')->nullable();
            }

            if (!Schema::hasColumn('salary_advance_requests', 'deduction_month')) {
                $table->integer('deduction_month')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_advance_requests', function (Blueprint $table) {
            if (Schema::hasColumn('salary_advance_requests', 'advance_date')) {
                $table->dropColumn('advance_date');
            }

            if (Schema::hasColumn('salary_advance_requests', 'deduction_month')) {
                $table->dropColumn('deduction_month');
            }
        });
    }
};
