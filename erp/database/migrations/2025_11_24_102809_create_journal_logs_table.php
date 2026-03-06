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
        Schema::create('journal_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_id')->nullable();
            $table->enum('action', ['created', 'updated', 'deleted']);
            $table->json('log_values')->nullable()->comment('all relation must be added with name not id only, you must send key this mean column name and value this mean the data value');
            $table->timestamp('log_timestamp')->nullable()->comment('exact time of action');
            $table->integer('created_by')->nullable()->change();
            $table->string('created_by_type')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('journal_id')->references('id')->on('journals')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_logs');
    }
};
