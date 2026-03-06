<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_order_reject_reasons', function (Blueprint $table) {
            // Drop the old column
            $table->dropColumn('reason');

            // Add the new bilingual columns
            $table->string('name_en');
            $table->string('name_ar');
        });
    }

    public function down(): void
    {
        Schema::table('supply_order_reject_reasons', function (Blueprint $table) {
            // Revert back to the single column
            $table->dropColumn(['name_en', 'name_ar']);
            $table->string('reason')->after('id');
        });
    }
};
