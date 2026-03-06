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
            $table->unsignedBigInteger('journal_id')->nullable()->after('journal_entry_id');
            $table->foreign('journal_id')->references('id')->on('journals')->onUpdate('cascade');
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
