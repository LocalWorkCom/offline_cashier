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
        Schema::table('employee_document_managements', function (Blueprint $table) {
            $table->renameColumn('document_management_id', 'document_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('employee_document_managements', function (Blueprint $table) {
            $table->renameColumn('document_type_id', 'document_management_id');
        });
    }
};
