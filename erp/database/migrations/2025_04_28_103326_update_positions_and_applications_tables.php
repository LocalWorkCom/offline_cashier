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
        // Update positions table
        Schema::table('positions', function (Blueprint $table) {
            $table->string('application_path')->nullable(); // adjust 'after' as you wish
            $table->string('application_link')->nullable()->after('application_path');
        });

        // Update applications table
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'job_title')) {
                $table->renameColumn('job_title', 'position_id');
            }
            if (Schema::hasColumn('applications', 'questions_file')) {
                $table->removeColumn('questions_file');
            }
        });

        // separate Schema::table
        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedBigInteger('position_id')->change();
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');

            $table->enum('status', [
                'new_request',
                'accepted',
                'pending_interview',
                'rejected',
                'incomplete_information',
                'on_hold'
            ])->default('new_request')->after('position_id');

            $table->dateTime('datetime')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('application_path');
            $table->dropColumn('application_link');
        });

        // Rollback applications table changes
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'position_id')) {
                $table->dropForeign(['position_id']);
                $table->renameColumn('position_id', 'job_title');
            }

            if (Schema::hasColumn('applications', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('applications', 'datetime')) {
                $table->dropColumn('datetime');
            }

            $table->string('questions_file')->nullable();
        });
    }
};
