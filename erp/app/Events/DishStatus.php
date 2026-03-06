<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DishStatus implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $dish;
    public $branch_id;
    public $status;

    public function __construct($dish, $branch, $status)
    {
        $this->dish = $dish;
        $this->branch_id = $branch;
        $this->status = $status;
        Log::info("Broadcasting dish: {$status} for branch {$branch} to dish {$dish->id}");
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('dish-' . $this->branch_id),
        ];
    }
    public function broadcastAs()
    {
        return 'dish-change';
    }
    public function broadcastWith()
    {
        $controller = app(\App\Http\Controllers\Api\CashierAPIs\MenuDishesController::class);
        $request = request();
        $request->dishId = $this->dish->id;
        $dishDetails = $controller->menuDishesDetails($request);

        if (is_object($dishDetails) && isset($dishDetails->original)) {
            $originalData = (array) $dishDetails->original;
            if (isset($originalData['data'])) {
                $dishData = $originalData['data'];
            } else {
                $dishData = [];
            }
        } else {
            $dishData = [];
        }

        return [
            'dish' => $dishData,
            'branch_id' => $this->branch_id,
            'status' => $this->status,
        ];
    }
}
