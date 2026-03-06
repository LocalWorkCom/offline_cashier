<?php

namespace App\Events;

use App\Models\TableReservation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationAutoCancelled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reservation;

    public function __construct(TableReservation $reservation)
    {
        $this->reservation = $reservation;
    }
}
