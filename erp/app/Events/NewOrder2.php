<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewOrder2  implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public  $employee;
    public  $branchId;

    public array $data;
    /**
     * Create a new event instance.
     */
    public function __construct($employee, $branchId, array $data)
    {
        $this->employee = $employee;
        $this->branchId = $branchId;
        $this->data = $data;
        Log::info("Broadcasting new order 2 for employee ID: {$employee} in branch ID: {$branchId}", [
            'order_data' => $data
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('newOrder2-' . $this->employee . '-branch-' . $this->branchId),
        ];
    }

    public function broadcastAs()
    {
        return 'new-order-added2';
    }
}
