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
        Schema::table('filed_of_studies', function (Blueprint $table) {
            // Drop foreign keys if they exist
            $table->dropForeign(['created_by']);
            $table->dropForeign(['modified_by']);
            $table->dropForeign(['deleted_by']);

            // Add polymorphic type columns
            $table->string('created_by_type')->nullable()->after('created_by');
            $table->string('modified_by_type')->nullable()->after('modified_by');
            $table->string('deleted_by_type')->nullable()->after('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filed_of_studies', function (Blueprint $table) {
            // Remove polymorphic type columns
            $table->dropColumn(['created_by_type', 'modified_by_type', 'deleted_by_type']);

            // Re-add foreign key constraints
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('modified_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }
};
