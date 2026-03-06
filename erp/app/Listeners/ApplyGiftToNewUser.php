<?php

namespace App\Listeners;

use App\Models\Gift;
use App\Models\UserGift;
use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ApplyGiftToNewUser
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        $gift = Gift::where('name', 'Welcome Gift')->first();

        if ($gift) {
            UserGift::create([
                'user_id' => $event->user->id,
                'gift_id' => $gift->id,
                'used' => false
            ]);
        }
    }
}
