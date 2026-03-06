<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class orderChangeStatusForClient implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $data;
    /**
     * Create a new event instance.
     */
    public function __construct( array $data)
    {
        Log::info("In orderChangeStatus event, data is " . json_encode($data));
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('order-channel'),
        ];
    }

    public function broadcastAs()
    {
        return 'order-status-update';
    }
    public function broadcastWith(): array
    {
        return $this->data;
    }
}
