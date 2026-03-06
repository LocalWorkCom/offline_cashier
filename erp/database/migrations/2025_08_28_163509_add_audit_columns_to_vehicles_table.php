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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('license');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
            $table->string('created_type')->nullable()->after('created_by');
            $table->string('updated_type')->nullable()->after('updated_by');
            $table->string('deleted_type')->nullable()->after('deleted_by');

            $table->softDeletes()->after('deleted_type'); // adds deleted_at column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by', 'type']);
        });
    }
};
