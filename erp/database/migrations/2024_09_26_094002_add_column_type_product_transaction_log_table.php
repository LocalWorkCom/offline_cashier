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
        Schema::table('product_transaction_logs', function (Blueprint $table) {
            $table->enum('type', [1,2])->default(1)->comment('1 for outgoing, 2 for incoming');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_transaction_logs', function (Blueprint $table) {
            //
        });
    }
};
