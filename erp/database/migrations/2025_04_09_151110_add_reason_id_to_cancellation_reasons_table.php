<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cancellation_reasons', function (Blueprint $table) {
            $table->unsignedBigInteger('reason_id')->nullable()->after('order_id');

            $table->foreign('reason_id')
                  ->references('id')
                  ->on('order_cancellation_reasons')
                  ->onDelete('set null'); // or 'cascade' / 'restrict' based on your logic
        });
    }

    public function down(): void
    {
        Schema::table('cancellation_reasons', function (Blueprint $table) {
            $table->dropForeign(['reason_id']);
            $table->dropColumn('reason_id');
        });
    }
};
