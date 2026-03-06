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
        Schema::table('recipes', function (Blueprint $table) {
 if (!Schema::hasColumn('recipes', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }

            if (!Schema::hasColumn('recipes', 'modified_by')) {
                $table->unsignedBigInteger('modified_by')->nullable()->after('created_by');
            }

            if (!Schema::hasColumn('recipes', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('modified_by');
            }
            // Add type columns (string or enum)
            $table->string('created_by_type')->nullable()->after('created_by');
            $table->string('modified_by_type')->nullable()->after('modified_by');
            $table->string('deleted_by_type')->nullable()->after('deleted_by');        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            //
        });
    }
};
