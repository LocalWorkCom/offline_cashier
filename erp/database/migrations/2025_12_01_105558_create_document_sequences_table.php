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
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_type_id')->constrained('document_name_types');

            // Naming convention fields
            $table->string('prefix')->nullable();             // PR-, INV-, PO-
            $table->string('suffix')->nullable();             // optional custom suffix

            $table->enum('numbering_style', ['sequential', 'alphanumeric', 'numarical'])->nullable();

            $table->string('format')->nullable()->comment('be like 01,001,0001,00001');

            $table->boolean('include_year')->default(false);  // include year?
            $table->enum('year_format', ['YYYY', 'YY'])->nullable();

            $table->boolean('include_branch')->default(false);
            $table->foreignId('branch_id')->constrained('branches')->nullable();

            // Sequence logic
            $table->integer('current_sequence')->default(0);       // last used number
            $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('employees')->nullOnDelete();

            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
