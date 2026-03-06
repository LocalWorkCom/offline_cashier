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
        Schema::table('purchase_requests', function (Blueprint $table) {

            // Make columns nullable
            $table->unsignedBigInteger('store_id')->nullable()->change();
            $table->unsignedBigInteger('created_by')->nullable()->change();

            // New columns
            $table->enum('type', ['direct', 'indirect'])->nullable()->after('created_by');
            $table->enum('priority', ['Urgent', 'High', 'Medium', 'Low'])->nullable()->after('type');
            $table->date('receive_date')->nullable()->after('priority');
            $table->text('note')->nullable()->after('receive_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
