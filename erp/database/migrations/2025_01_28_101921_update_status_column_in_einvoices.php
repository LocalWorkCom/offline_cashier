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
        Schema::table('einvoices', function (Blueprint $table) {
            $table->string('status')->nullable()->default(null)->change();
            $table->string('uuid')->nullable()->default(null)->change();
            $table->enum('invoice_type', ['i', 'c'])
                ->default('i')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('einvoices', function (Blueprint $table) {
            $table->string('status')->nullable(false)->default('')->change();
            $table->string('uuid')->nullable(false)->default('')->change();
            $table->enum('invoice_type', ['i', 'c'])
                ->default(null)
                ->change();
        });
    }
};
