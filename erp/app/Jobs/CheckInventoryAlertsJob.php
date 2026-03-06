<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Product;
use App\Models\Alert;
use App\Models\InventoryEmployee;
use App\Models\InventorySetting;
use App\Models\ProductBrand;
use App\Notifications\InventoryAlertNotification;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class CheckInventoryAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $settings = InventorySetting::first();

        if (!$settings || !$settings->receive_notifications) {
            return;
        }

        $thresholdDays = $settings->notify_before_expiration ? $settings->no_of_days : 0;
        $notificationRecipient = $settings->notification_recipient;
        $today = Carbon::today();

        $products = ProductBrand::all();

        foreach ($products as $product) {
            $getProductQuantity = getProductQuantity($product->id, 'base', null, false, true);

            foreach ($getProductQuantity['barcodes'] as $barcodeData) {
                $expiry_date = $barcodeData['expiry'];

                if ($expiry_date && 
                    $expiry_date->greaterThanOrEqualTo($today) && 
                    $today->diffInDays($expiry_date, false) <= $thresholdDays) {

                    $employees = InventoryEmployee::where('store_id', $product->productStores->first()->store_id)->get();

                    foreach ($employees as $employee) {
                        if (
                            ($notificationRecipient == 'inventory manager' && $employee->hasRole('Inventory_Manager')) ||
                            ($notificationRecipient == 'warehouse_staff')
                        ) {
                            $this->sendNotification($product, $expiry_date, $employee);
                        }
                    }
                }
            }
        }
    }

    protected function sendNotification($product, $expiry_date, $employee)
    {
        $lang = app()->getLocale();
        $employee_full_name = $employee->first_name . ' ' . $employee->last_name;

        $body_ar = "المنتج {$product->name} سينتهي في {$expiry_date->format('Y-m-d')} بواسطة {$employee_full_name}.";
        $body_en = "Product {$product->name} will expire on {$expiry_date->format('Y-m-d')} by {$employee_full_name}.";

        $title_ar = 'تنبيه انتهاء صلاحية المنتج';
        $title_en = 'Product Expiry Alert';

        if ($employee->device_token) {
            send_push_notification(
                $employee->device_token,
                $body_ar,
                $body_en,
                $title_ar,
                $title_en,
                'inventory_alert',
                $employee->id,
                $employee->id,
                $employee->id,
                $lang,
                7
            );
        }
    }
}
