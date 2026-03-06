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
        Schema::create('order_waste_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_waste_id')->constrained()->cascadeOnUpdate();
            $table->string('action'); // create, update, delete, status_change

            // Waste record fields
            $table->foreignId('order_id')->nullable();
            $table->foreignId('order_detail_id')->nullable();
            $table->foreignId('order_addon_id')->nullable();
            $table->integer('original_quantity')->nullable();
            $table->integer('waste_quantity')->nullable();
            $table->foreignId('waste_reason_id')->nullable();
            $table->string('type')->nullable(); // temporary, permanent
            $table->string('flag')->nullable(); // dish, addon
            $table->boolean('reused')->nullable();
            $table->text('note')->nullable();

            // Changed fields tracking (for updates)
            $table->string('changed_fields')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('modified_by')->nullable();
            $table->foreignId('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_waste_logs');
    }
};
