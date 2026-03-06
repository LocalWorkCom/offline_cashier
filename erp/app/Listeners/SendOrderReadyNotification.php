<?php

namespace App\Listeners;

use App\Events\OrderReadyForPickup;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class SendOrderReadyNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     */
    public function handle(OrderReadyForPickup $event): void
    {
        $order = $event->order;
        $lang = $event->lang;
//        $user = User::find(9);
        $user = User::find($order->client_id);
        $user_fcm = $user->fcm_token;
        $user_id = $user->id;
//        $fullUrl = url()->current();
//        $apiBaseUrl = Str::contains($fullUrl, '/api')
//            ? Str::before($fullUrl, '/api')
//            : (Str::contains($fullUrl, '/dashboard')
//                ? Str::before($fullUrl, '/dashboard')
//                : $fullUrl);

//        $url = $apiBaseUrl.'/dashboard/order/show/' . $order->id;

        send_push_notification(
            $user_fcm,
            'طلبك رقم '. $order->order_number . ' جاهز للاستلام فرع ' . $order->branch->name_ar,
            'Your order '. $order->order_number. ' is ready for pickup in branch ' . $order->branch->name_en,
            'طلبك جاهز للاستلام',
            'Your order is ready for pickup',
            'client',
            $user_id,
            $user_id,
            $order->id,
            $lang,
            'order',
        );
    }
}
