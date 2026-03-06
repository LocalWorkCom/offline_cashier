<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUniqueFromContactInformationSettings extends Migration
{
    public function up()
    {
        Schema::table('contact_information_settings', function (Blueprint $table) {
            // Drop unique index for phone_number if it exists
            $table->dropUnique('contact_information_settings_phone_number_unique');

            // Drop unique index for email if it exists
            $table->dropUnique('contact_information_settings_email_unique');
        });
    }

    public function down()
    {
        Schema::table('contact_information_settings', function (Blueprint $table) {
            // Re-add the unique constraints if you rollback
            $table->unique('phone_number');
            $table->unique('email');
        });
    }
}

