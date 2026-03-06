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
        Schema::table('return_invoice_requests', function (Blueprint $table) {
            $table->enum('request_type', ['partial', 'full'])->default('partial')->nullable()->after('request_num')->comment('full if return all order, partial if return part from order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_invoice_requests', function (Blueprint $table) {
            //
        });
    }
};
