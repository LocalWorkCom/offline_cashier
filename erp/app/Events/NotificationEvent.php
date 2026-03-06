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

class NotificationEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public  $userId;
    public  $flag;

    public array $data;
    /**
     * Create a new event instance.
     */
    public function __construct($userId, $flag, array $data)
    {
        $this->userId = $userId;
        $this->flag = $flag;

        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        Log::info('Broadcasting notification on channel', [
            'data' => $this->data,
            'employee_id' => $this->userId,
            'flag' => $this->flag
        ]);
        return [
            new Channel('notification-' . $this->userId . '-'.$this->flag),
        ];
    }

    public function broadcastAs()
    {
        return 'Notifications';
    }
    public function broadcastWith(): array
    {
        return $this->data;
    }
}
