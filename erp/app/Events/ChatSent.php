<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $receiver;
    public $sender;
    public ?string $message;
    public string $guardType;
    public $type;
    public $channel;
    public $sent_at;
    public $is_read;
    public $idMessage;

    public function __construct($receiver, $sender, $idMessage, $message, $guardType, $type = 'text', $channel)
    {
        $this->receiver = $receiver;
        $this->sender = $sender;
        $this->channel = $channel;
        $this->idMessage = $idMessage;
        $this->message = $message;
        $this->guardType = $guardType;
        $this->type = $type;
        $this->is_read = false;
        $this->sent_at = now();
    }
    public function broadcastOn(): array
    {
        // Use a unique channel for each guard type
        return [
            new Channel('chat-' . $this->channel),
        ];
    }

    public function broadcastAs()
    {
        return 'chatMessage';
    }
}
