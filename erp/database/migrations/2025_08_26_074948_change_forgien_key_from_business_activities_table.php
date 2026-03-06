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
        Schema::table('business_activities', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['modified_by']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['deleted_by']);
            
            // Optional: If you want to keep the columns but remove foreign keys
            // The columns will remain as regular integer columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_activities', function (Blueprint $table) {
            // Re-add foreign key constraints (adjust table names as needed)
            $table->foreign('modified_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }
};