<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            // Drop old name column if exists
            if (Schema::hasColumn('audits', 'name')) {
                $table->dropColumn('name');
            }

            // Add audit_number column
            $table->string('audit_number')->unique()->after('id')->comment('Auto-generated audit number');
        });
    }

    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->dropColumn('audit_number');
        });
    }
};
