<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateCategoriesTable extends Migration
{
    public function up()
    {
        // 1️⃣ Check and drop foreign key safely
        $foreignKeys = DB::select("SHOW CREATE TABLE categories");
        $foreignSQL = $foreignKeys[0]->{'Create Table'};

        if (str_contains($foreignSQL, 'FOREIGN KEY (`parent_id`)')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropForeign(['parent_id']);
            });
        }

        // 2️⃣ Drop old parent_id column if it exists
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'parent_id')) {
                $table->dropColumn('parent_id');
            }
        });

        // 3️⃣ Add new JSON parent_id column
        Schema::table('categories', function (Blueprint $table) {
            $table->json('parent_id')->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'parent_id')) {
                $table->dropColumn('parent_id');
            }

            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            // Optional: re-add the foreign key if it originally existed
            // $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }
}
