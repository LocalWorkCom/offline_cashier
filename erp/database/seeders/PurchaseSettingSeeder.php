<?php

namespace Database\Seeders;

use App\Models\PurchaseSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        PurchaseSetting::firstOrCreate(
            ['id' => 1],
            [
                'auto_creation_po' => false,
                'notify_PM' => false,
                'notify_EMPO' => false,
                'updated_by' => null,
            ]
        );
    }
}
