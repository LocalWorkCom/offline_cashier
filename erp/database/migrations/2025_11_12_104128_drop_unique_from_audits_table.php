<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            // 🔹 Replace 'unique_column_name' with your actual column name(s)
            $table->dropUnique(['audit_number']); 
            
            // or if the unique index has a specific name:
            // $table->dropUnique('audits_unique_column_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            // Restore the unique constraint if needed
            $table->unique('audit_number');
        });
    }
};
