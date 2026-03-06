<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('chef_cuisine_categories', function (Blueprint $table) {
            $table->json('dishes')->nullable()->after('cuisine_category_id')->comment('dishes will store an array of dish IDs, or [-1] for "all dishes"');;
        });
    }

    public function down()
    {
        Schema::table('chef_cuisine_categories', function (Blueprint $table) {
            $table->dropColumn('dishes');
        });
    }
};