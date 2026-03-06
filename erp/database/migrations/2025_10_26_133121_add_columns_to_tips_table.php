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
        Schema::table('tips', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('menus_integration_id')->nullable();
            $table->string('payment_status_menu_integration')->nullable();
            $table->string('payment_method_menu_integration')->nullable();
            $table->foreign('menus_integration_id')
                ->references('id')
                ->on('menus_integrations')
                ->onUpdate('cascade')
                ->onDelete('set null');
                });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tips', function (Blueprint $table) {
            //
        });
    }
};
