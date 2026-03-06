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
        Schema::table('order_cancellation_reasons', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('id');
            $table->string('created_by_type')->nullable()->after('created_by');

            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by_type');
            $table->string('updated_by_type')->nullable()->after('updated_by');

            $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by_type');
            $table->string('deleted_by_type')->nullable()->after('deleted_by');
                        $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_cancellation_reasons', function (Blueprint $table) {
            //
        });
    }
};
