<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE payroll_sheets 
            MODIFY COLUMN status ENUM('draft', 'review', 'awaiting_finance', 'returned', 'approved') 
            NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE payroll_sheets 
            MODIFY COLUMN status ENUM('draft', 'awaiting_finance', 'returned', 'approved') 
            NOT NULL DEFAULT 'draft'
        ");
    }
};
