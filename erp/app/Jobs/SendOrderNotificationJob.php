<?php

namespace App\Jobs;

use App\Services\ClientServices\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId, $branchId, $userType, $orderType, $createdBy, $lang;

    public function __construct($orderId, $branchId, $userType, $orderType, $createdBy, $lang)
    {
        $this->orderId = $orderId;
        $this->branchId = $branchId;
        $this->userType = $userType;
        $this->orderType = $orderType;
        $this->createdBy = $createdBy;
        $this->lang = $lang;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            log::info('open job notifiy');
            // Call your existing notification method
            app(OrderService::class)
                ->send_notification(
                    $this->orderId,
                    $this->branchId,
                    $this->userType,
                    $this->orderType,
                    $this->createdBy,
                    $this->lang
                );
            sendToKitchen($this->orderId,  $this->lang);
        } catch (\Throwable $e) {
            Log::error("SendOrderNotificationJob failed: " . $e->getMessage(), [
                'order_id' => $this->orderId
            ]);
        }
    }
}
