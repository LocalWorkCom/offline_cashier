<?php

namespace App\Listeners;

use App\Events\ReservationAutoCancelled;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class SendReservationCancelledNotification
{
    public function __construct()
    {
        //
    }

    public function handle(ReservationAutoCancelled $event): void
    {
        Log::info('ReservationAutoCancelled event triggered for reservation ID: ' . $event->reservation->id);
        $reservation = $event->reservation;
        $user = User::find($reservation->client_id);

        if (!$user) {
            return;
        }
        Log::info('Sending notification for user: ' . $user->id);
        $url = URL::route('orders.show');

        send_push_notification(
            $user->fcm_token,
            'تم الغاء حجز الطاولة لعدم الحضور في الوقت المحدد',
            'Table Reservation is Cancelled For Not Showing Up On Time ',
            'تم إلغاء الحجز',
            'Table Reservation Cancelled',
            'reservation_cancelled',
            $user->id,
            $user->id,
            $reservation->id,
            app()->getLocale(),
            $url,'table'
        );
    }
}
