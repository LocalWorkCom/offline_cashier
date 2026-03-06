<?php

namespace App\Providers;

use App\Events\OrderReadyForPickup;
use App\Events\UserRegistered;
use App\Listeners\ApplyGiftToNewUser;
use App\Listeners\SendOrderReadyNotification;
use App\Listeners\SendReservationCancelledNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\ProductTransactionEvent;
use App\Listeners\AddProductTransactionEvent;
use App\Events\OrderTransactionEvent;
use App\Listeners\AddOrderTransactionListener;
use App\Events\OrderRefundTransactionEvent;
use App\Listeners\AddOrderRefundTransactionListener;
use App\Events\ProductTransactionLogEvent;
use App\Events\ReservationAutoCancelled;
use App\Listeners\AddProductTransactionLogListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ProductTransactionEvent::class => [
            AddProductTransactionEvent::class,
        ],
        OrderTransactionEvent::class => [
            AddOrderTransactionListener::class,
        ],
        OrderRefundTransactionEvent::class => [
            AddOrderRefundTransactionListener::class,
        ],
        ProductTransactionLogEvent::class => [
            AddProductTransactionLogListener::class,
        ],
        UserRegistered::class => [
            ApplyGiftToNewUser::class,
        ],
        OrderReadyForPickup::class => [
            SendOrderReadyNotification::class,
        ],
        ReservationAutoCancelled::class => [
            SendReservationCancelledNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
