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
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['modified_by']);
            $table->dropForeign(['deleted_by']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->bigInteger('created_by')->nullable()->change();
            $table->bigInteger('modified_by')->nullable()->change();
            $table->bigInteger('deleted_by')->nullable()->change();
            $table->enum('make_type', ['waiter','cashier','customer_service','app','site','admin'])->after('note');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
        });
    }
};
