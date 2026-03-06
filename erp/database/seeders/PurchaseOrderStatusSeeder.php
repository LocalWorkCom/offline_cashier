<?php

namespace Database\Seeders;

use App\Models\PurchaseOrderStatus;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseOrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['title_en' => 'draft', 'title_ar' => 'مسوده', 'type' => 1, 'parent_id' => null, 'child_id' => 2, 'base_status' => null],
            ['title_en' => 'submitted', 'title_ar' => 'تم التقديم', 'type' => 1, 'parent_id' => 1, 'child_id' => 3, 'base_status' => null],
            ['title_en' => 'approved', 'title_ar' => 'موافقة', 'type' => 1, 'parent_id' => 2, 'child_id' => 4, 'base_status' => null],
            ['title_en' => 'rejected', 'title_ar' => 'رفض', 'type' => 1, 'parent_id' => 3, 'child_id' => 5, 'base_status' => null],
            ['title_en' => 'received', 'title_ar' => 'تم الاستلام', 'type' => 1, 'parent_id' => 4, 'child_id' => 6, 'base_status' => null],
            ['title_en' => 'partially-received', 'title_ar' => 'تم الاستلام جزئياً', 'type' => 1, 'parent_id' => 5, 'child_id' => 4, 'base_status' => null],
            ['title_en' => 'not-received', 'title_ar' => 'لم يتم الاستلام', 'type' => 1, 'parent_id' => 6, 'child_id' => null, 'base_status' => null],
        ];

        $currentTimestamp = Carbon::now();

        foreach ($statuses as $status) {
            PurchaseOrderStatus::insert([
                'title_en'     => $status['title_en'],
                'title_ar'     => $status['title_ar'],
                'type'         => $status['type'],
                'parent_id'    => $status['parent_id'],
                'child_id'     => $status['child_id'],
                'base_status'  => $status['base_status'],
                'created_at'   => $currentTimestamp,
                'updated_at'   => $currentTimestamp,
            ]);
        }
    }
}
