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
        Schema::create('document_formats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_sequnce_id')->constrained('document_sequences');

            $table->text('logo_location')->nullable();

            $table->longText('header_text_en')->nullable();
            $table->longText('header_text_ar')->nullable();

            $table->longText('footer_text_en')->nullable();
            $table->longText('footer_text_ar')->nullable();

            $table->string('font_type')->default('Arial');
            $table->integer('font_size')->default(12);

            $table->longText('compliance_text_ar')->nullable();
            $table->longText('compliance_text_en')->nullable();

            $table->integer('signeter_count')->default(0);
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
        Schema::dropIfExists('document_formats');
    }
};
