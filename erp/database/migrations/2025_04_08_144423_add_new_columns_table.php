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
            $table->timestamp('last_message_at')->nullable()->after('status');
            $table->timestamp('auto_close_at')->nullable()->after('last_message_at');
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
