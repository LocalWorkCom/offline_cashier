<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class orderChangeStatus implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    protected $order;

    public function __construct($data, $order)
    {
        Log::info("In orderChangeStatus event, data is " . json_encode($data));
        $this->order = $order;
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */

    public function broadcastOn()
    {
        $channels = [
            new Channel('order-channel-' . $this->order->id),
            new Channel('order-channel'),
        ];

        if (!empty($this->order->client_id)) {
            $channels[] = new Channel('order-channel-' . $this->order->id . '-client-' . $this->order->client_id);
        }

        return $channels;
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
