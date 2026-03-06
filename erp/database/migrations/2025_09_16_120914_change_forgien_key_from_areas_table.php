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
        Schema::table('areas', function (Blueprint $table) {
            if (!Schema::hasColumn('areas', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }

            if (!Schema::hasColumn('areas', 'modified_by')) {
                $table->unsignedBigInteger('modified_by')->nullable()->after('created_by');
            }

            if (!Schema::hasColumn('areas', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('modified_by');
            }
            // Add type columns (string or enum)
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
        //
    }
};
