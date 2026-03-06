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
        Schema::table('chat_channels', function (Blueprint $table) {
            $table->timestamp('last_customer_service_response')->nullable();
            $table->timestamp('reassignment_at')->nullable();
            $table->timestamp('reassignment_from')->nullable();

            $table->boolean('needs_reassignment')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_channels', function (Blueprint $table) {
            //
        });
    }
};
