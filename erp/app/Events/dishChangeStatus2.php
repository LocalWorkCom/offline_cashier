<?php

namespace App\Events;

use App\Models\Dish;
use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class dishChangeStatus2 implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        // $this->recipients = $recipients;
        $this->data = $data;
    }

    public function broadcastOn(): array
    {
        Log::info("Broadcasting to public channel for order {$this->data['order_id']} that data is " . json_encode($this->data));
        return [
            new Channel("dish-order-statuses-changed2"),
        ];
    }


    public function broadcastAs()
    {
        return 'Dish-status2';
    }
}
