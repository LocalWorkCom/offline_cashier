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
        Schema::table('journal_entry_details', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->after('journal_id');
            $table->unsignedBigInteger('vendor_id')->nullable()->after('customer_id');
            $table->foreign('customer_id')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entry_details', function (Blueprint $table) {
            //
        });
    }
};
