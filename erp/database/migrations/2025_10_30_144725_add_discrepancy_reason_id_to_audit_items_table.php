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
        Schema::table('audit_items', function (Blueprint $table) {
            $table->unsignedBigInteger('discrepancy_reason_id')->nullable()->after('id');

            // Add foreign key constraint
            $table->foreign('discrepancy_reason_id')
                ->references('id')
                ->on('discrepancy_reasons')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_items', function (Blueprint $table) {
            $table->dropForeign(['discrepancy_reason_id']);
            $table->dropColumn('discrepancy_reason_id');
        });
    }
};
