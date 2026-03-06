<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->dropColumn('table_cancelation_value_type');
        });
    }

    public function down(): void
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->tinyInteger('table_cancelation_value_type')->default(0);
        });
    }
};

