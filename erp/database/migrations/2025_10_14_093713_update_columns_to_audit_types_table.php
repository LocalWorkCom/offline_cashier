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
        Schema::table('audit_types', function (Blueprint $table) {
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('scheduled_regularly')->nullable();
            $table->dropColumn('title');
            $table->dropColumn('status');
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->string('modified_by_type')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_types', function (Blueprint $table) {
            //
        });
    }
};
