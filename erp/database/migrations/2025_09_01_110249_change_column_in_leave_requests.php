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
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('agreement');
            $table->dropColumn('agreement_by');
            $table->dropColumn('agreement_date');
            $table->dropColumn('agreement_resone');
            $table->dropColumn('stauts');
            $table->enum('status', ['pending', 'processing', 'confirm', 'reject'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            //
        });
    }
};
