<?php

namespace App\Listeners;

use App\Events\ProductTransactionLogEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AddProductTransactionLogListener
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
    public function handle(ProductTransactionLogEvent $event): void
    {
        //
    }
}
