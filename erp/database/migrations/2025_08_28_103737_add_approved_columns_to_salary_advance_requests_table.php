<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_advance_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('approved_by_type')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('salary_advance_requests', function (Blueprint $table) {
            $table->dropColumn(['approved_by', 'approved_by_type']);
        });
    }
};
