<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('chat_channels', function (Blueprint $table) {
            $table->dropColumn([
                'last_customer_service_response',
                'reassignment_at',
                'needs_reassignment',
                'reassignment_from'
            ]);
        });
    }

    public function down()
    {
        Schema::table('chat_channels', function (Blueprint $table) {
            $table->timestamp('last_customer_service_response')->nullable();
            $table->timestamp('reassignment_at')->nullable();
            $table->boolean('needs_reassignment')->default(false);
            $table->unsignedBigInteger('reassignment_from')->nullable();
        });
    }
};
