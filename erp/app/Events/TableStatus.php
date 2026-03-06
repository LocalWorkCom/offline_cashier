<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TableStatus implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $table;
    public $branch;
    public $status;
    /**
     * Create a new event instance.
     */
    public function __construct($table , $branch ,$status = null)
    {

        $this->table = $table;
        $this->branch = $branch;
        $this->status = $status;
                 Log::info("Broadcasting TableStatus: {$status} for branch {$branch}");

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('table-' .$this->branch),
        ];
    }

    public function broadcastAs()
    {
        return 'tables';
    }

}
