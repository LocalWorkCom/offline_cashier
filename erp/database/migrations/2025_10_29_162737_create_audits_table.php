<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('name')->nullable();
            $table->enum('audit_type', ['periodic', 'surprise'])
                ->comment('Type of audit: periodic or surprise');

            // Timing
            $table->enum('period', ['daily', 'weekly', 'monthly'])
                ->nullable()
                ->comment('Only used for periodic audits');
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->date('audit_date')->nullable()->comment('Used for surprise audit');

            // Scope
            $table->enum('scope', ['full', 'partial', 'location'])->nullable();
            $table->enum('scope_type', ['zone', 'location', 'category', 'product', 'branch', 'store'])
                ->nullable()
                ->comment('Defines what the audit is targeting');
            $table->json('scope_ids')->nullable()
                ->comment('JSON array of IDs for the selected scope type (multi-select)');

            // Branch or store (for full audit)
            // $table->unsignedBigInteger('branch_id')->nullable();
            // $table->unsignedBigInteger('store_id')->nullable();

            // Status
            $table->enum('status', ['draft', 'pending', 'being_audited', 'Done'])
                ->default('draft');

            // Audit results or actions
            // $table->json('results')->nullable()->comment('Audit result data or differences');

            $table->integer('created_by')->nullable();
            $table->integer('modified_by')->nullable();
            $table->integer('deleted_by')->nullable();
          

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
