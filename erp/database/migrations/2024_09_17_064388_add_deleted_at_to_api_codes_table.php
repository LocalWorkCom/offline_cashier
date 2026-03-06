<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToApiCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('apicodes', function (Blueprint $table) {
            if (!Schema::hasColumn('apicodes', 'deleted_at')) {
                $table->softDeletes();  // Adds the 'deleted_at' column
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('apicodes', function (Blueprint $table) {
            // Remove the deleted_at column
            $table->dropSoftDeletes();
        });
    }
}
