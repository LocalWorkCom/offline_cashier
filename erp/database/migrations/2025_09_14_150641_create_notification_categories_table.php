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
        Schema::create('notification_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Make notify_type unsignedBigInteger but nullable first (no FK yet)
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('notify_type')->nullable()->change();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_categories');
    }
};
