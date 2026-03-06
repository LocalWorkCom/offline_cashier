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
        Schema::table('currency_exchanges', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['modified_by']);
            $table->dropForeign(['deleted_by']);
        });

        Schema::table('currency_exchanges', function (Blueprint $table) {

            $table->integer('created_by')->nullable()->change();
            $table->integer('modified_by')->nullable()->change();
            $table->integer('deleted_by')->nullable()->change();

            $table->string('created_by_type')->nullable();
            $table->string('modified_by_type')->nullable();
            $table->string('deleted_by_type')->nullable();

            $table->decimal('exchange_value', 15,2)->nullable()->change();
            $table->decimal('exchange_value_before', 15,2)->nullable()->after('exchange_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currency_exchanges', function (Blueprint $table) {
            //
        });
    }
};
