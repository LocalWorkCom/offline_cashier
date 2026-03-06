<?php

namespace Database\Seeders;

use App\Models\InventorySetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventorySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InventorySetting::insert([
            'notify_before_expiration' => 1, // 1 = yes, 0 = no
                'auto_purchase_order_on_low_stock' => 0,
                'notification_frequency' => 'daily', // or 'monthly'
                'no_of_days' => 7, // used only if daily
                'receive_notifications' => 1, // 1 = yes, 0 = no
                'notification_recipient' => 'Warehouse', // or another recipient type
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
