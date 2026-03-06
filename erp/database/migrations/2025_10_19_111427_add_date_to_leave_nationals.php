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
        Schema::table('leave_nationals', function (Blueprint $table) {
            $table->date('current_date')->nullable()->after('date');
            $table->date('holiday_date')->nullable()->after('current_date');
            $table->boolean('status')->nullable()->default(1)->after('holiday_date');
            $table->string('created_by_type')->nullable()->after('updated_at');
            $table->string('modified_by_type')->nullable()->after('created_by_type');
            $table->string('deleted_by_type')->nullable()->after('modified_by_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_nationals', function (Blueprint $table) {
            $table->dropColumn(['current_date', 'holiday_date', 'status']);
        });
    }
};
