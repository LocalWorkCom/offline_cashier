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
        Schema::create('chat_channels', function (Blueprint $table) {
            $table->id();

            // Polymorphic participants
            $table->unsignedBigInteger('initiator_id');
            $table->string('initiator_type')->comment('App\Models\User or App\Models\Employee');

            $table->unsignedBigInteger('participant_id');
            $table->string('participant_type')->comment('App\Models\User or App\Models\Employee');

            // Channel metadata
            $table->enum('status', ['open', 'closed', 'pending'])->default('open');

            // Timestamps
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['initiator_id', 'initiator_type']);
            $table->index(['participant_id', 'participant_type']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_client_channels');
    }
};
