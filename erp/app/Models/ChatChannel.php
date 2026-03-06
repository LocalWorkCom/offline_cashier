<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChatChannel extends Model
{
    protected $fillable = [
        'initiator_id',
        'initiator_type',
        'participant_id',
        'participant_type',
        'chat_with',
        'status',
        'started_at',
        'closed_at',
        'auto_close_at',
        'last_message_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'closed_at' => 'datetime',
        'auto_close_at' => 'datetime',
        'last_message_at' => 'datetime',
    ];

    /**
     * Get the initiator of the chat (could be User or Employee)
     */
    public function initiator(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the participant of the chat (could be User or Employee)
     */
    public function participant(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all messages in this channel
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'chat_id');  
    }


    public function isEmployeeInitiator()
    {
        return $this->initiator_type === 'driver' || $this->initiator_type === 'customer_service';
    }

    /**
     * Check if the participant is an employee
     */
    public function isEmployeeParticipant()
    {
        return $this->participant_type === 'driver' || $this->participant_type === 'customer_service';
    }
}
