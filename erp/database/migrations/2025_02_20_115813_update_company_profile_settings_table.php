<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('company_profile_settings', function (Blueprint $table) {
            // Check if the column exists before attempting to drop it
            if (Schema::hasColumn('company_profile_settings', 'business_activity')) {
                $table->dropColumn('business_activity');
            }
        });

        Schema::table('company_profile_settings', function (Blueprint $table) {
            // Check again before adding the column
            if (!Schema::hasColumn('company_profile_settings', 'business_activity')) {
                $table->unsignedBigInteger('business_activity')->nullable()->after('description_en');
                $table->foreign('business_activity')->references('id')->on('business_activities')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('company_profile_settings', function (Blueprint $table) {
            // Drop the foreign key only if it exists
            if (Schema::hasColumn('company_profile_settings', 'business_activity')) {
                $table->dropForeign(['business_activity']);
                $table->dropColumn('business_activity');
            }

            // Re-add the original column
            $table->string('business_activity')->nullable();
        });
    }
};
