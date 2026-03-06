<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_policies', function (Blueprint $table) {
            $table->boolean('table_cancelation_value_type')->default(0)->after('full_payment_required'); // Adjust 'after()' as needed
        });
    }

    public function down(): void
    {
        Schema::table('payment_policies', function (Blueprint $table) {
            $table->dropColumn('table_cancelation_value_type');
        });
    }
};
