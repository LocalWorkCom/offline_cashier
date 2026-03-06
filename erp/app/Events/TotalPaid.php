<?php

namespace App\Events;

use App\Models\Employee;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TotalPaid implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public  $cashier;
    public array $data;
    /**
     * Create a new event instance.
     */
    public function __construct( $cashier, array $data)
    {
        $this->cashier = $cashier;
        $this->data = $data;
        Log::info('total paid info to cashier'. $this->cashier.'data' . json_encode($this->data));

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('total-paid-' . $this->cashier),
        ];
    }

    public function broadcastAs()
    {
        return 'Cashier-total-paid';
    }
}
