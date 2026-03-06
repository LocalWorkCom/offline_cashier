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
        Schema::table('direct_supply_permission_status_settings', function (Blueprint $table) {
            // Remove the flag column
            $table->dropColumn('flag');
            
            // Add new columns
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->enum('type', ['start', 'intermediate', 'final'])->default('intermediate');
            $table->json('previous_statuses')->nullable()->comment('Allowed previous status IDs');
            $table->json('next_statuses')->nullable()->comment('Allowed next status IDs');
            $table->enum('behavior', ['manual', 'automatic'])->default('manual');
            $table->boolean('active')->default(true);
            
            // Add indexes for better performance
            $table->index('type');
            $table->index('behavior');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('direct_supply_permission_status_settings', function (Blueprint $table) {
            // Restore flag column
            $table->string('flag')->nullable();
            
            // Remove new columns
            $table->dropColumn([
                'description_ar',
                'description_en',
                'type',
                'previous_statuses',
                'next_statuses',
                'behavior',
                'active'
            ]);
            
            // Drop indexes
            $table->dropIndex(['type']);
            $table->dropIndex(['behavior']);
            $table->dropIndex(['active']);
        });
    }
};