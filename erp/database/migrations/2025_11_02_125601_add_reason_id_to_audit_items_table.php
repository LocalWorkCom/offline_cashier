<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_items', function (Blueprint $table) {
            // Add nullable reason_id with foreign key
            $table->foreignId('reason_id')
                ->nullable()
                ->after('unit_id')
                ->constrained('discrepancy_reasons')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('audit_items', function (Blueprint $table) {
            $table->dropForeign(['reason_id']);
            $table->dropColumn('reason_id');
        });
    }
};
