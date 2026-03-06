<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('product_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('product_transactions', 'type')) {
                $table->enum('type', ['in', 'out'])
                      ->default('in');
                     
            }
        });
    }

    public function down()
    {
        Schema::table('product_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('product_transactions', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
