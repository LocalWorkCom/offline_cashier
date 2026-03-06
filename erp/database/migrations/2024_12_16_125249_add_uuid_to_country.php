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
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
        });

        Schema::table('leave_settings', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
        });

        Schema::table('leave_nationals', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
        });

        Schema::table('store_transaction_details', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            //$table->dropForeign(['currency_code']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
        });
    
        Schema::table('countries', function (Blueprint $table) {
            $table->dropPrimary();
            $table->uuid('id')->primary()->unique()->index()->change();
        });
    
        Schema::table('branches', function (Blueprint $table) {
            $table->char('country_id', 36)->nullable()->change();
            $table->foreign('country_id')->references('id')->on('countries')->onUpdate('cascade');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->char('country_id', 36)->nullable()->change();
            $table->foreign('country_id')->references('id')->on('countries')->onUpdate('cascade');
        });

        Schema::table('leave_nationals', function (Blueprint $table) {
            $table->char('country_id', 36)->nullable()->change();
            $table->foreign('country_id')->references('id')->on('countries')->onUpdate('cascade');
        });

        Schema::table('leave_settings', function (Blueprint $table) {
            $table->char('country_id', 36)->nullable()->change();
            $table->foreign('country_id')->references('id')->on('countries')->onUpdate('cascade');
        });

        Schema::table('store_transaction_details', function (Blueprint $table) {
            $table->char('country_id', 36)->nullable()->change();
            $table->foreign('country_id')->references('id')->on('countries')->onUpdate('cascade');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->char('currency_code', 36)->nullable()->change();
            $table->foreign('currency_code')->references('id')->on('countries')->onUpdate('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->char('country_id', 36)->nullable()->change();
            $table->foreign('country_id')->references('id')->on('countries')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->id()->change();
        });
    }
};
