<?php

namespace App\Events;

use App\Models\Employee;
use App\Models\Table;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WaiterNotify implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Employee $waiter;
    public Table $table;

    public array $data;
    /**
     * Create a new event instance.
     */
    public function __construct(Employee $waiter, Table $table, array $data)
    {
        $this->waiter = $waiter;
        $this->table = $table;

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
            new Channel('table-status' . $this->table->id),
        ];
    }

    public function broadcastAs()
    {
        return 'Waiter-requests';
    }
    public function broadcastWith(): array
    {
        return $this->data;
    }
}
