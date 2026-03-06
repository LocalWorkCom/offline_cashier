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

class ChefNotify2 implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public  $headChef;
    public array $data;
    /**
     * Create a new event instance.
     */
    // public function __construct(Employee $headChef, array $data)
    // {
    //     $this->headChef = $headChef;
    //     $this->data = $data;
    // }
    public function __construct(int $chef, array $data)
    {
        $this->headChef = $chef;
        $this->data = $data;
    }


    public function broadcastOn(): array
    {
        return [
            new Channel('head-chef2' . $this->headChef),
        ];
    }

    public function broadcastAs()
    {
        return 'Chef-dishes2';
    }
}
