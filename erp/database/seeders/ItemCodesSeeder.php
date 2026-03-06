<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItemCodesSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'itemCode' => 'EG-675170532-100',
                'codeName' => 'Beverages Variety Packs',
                'codeNameAr' => 'مشروبات متنوعة',
                'description' => '',
                'descriptionAr' => '',
                'parentCode' => '10000623',
                'activeFrom' => Carbon::parse('2025-04-07'),
            ],
            [
                'itemCode' => 'EG-675170532-101',
                'codeName' => 'Various marine fish',
                'codeNameAr' => 'اسماك بحرية متنوعة',
                'description' => '',
                'descriptionAr' => '',
                'parentCode' => '10000626',
                'activeFrom' => Carbon::parse('2025-04-07'),
            ],
            [
                'itemCode' => 'EG-675170532-103',
                'codeName' => 'Lamb - Prepared/Processed',
                'codeNameAr' => 'مجبوس لحم ضاني او دجاج',
                'description' => '',
                'descriptionAr' => '',
                'parentCode' => '10006294',
                'activeFrom' => Carbon::parse('2025-04-07'),
            ],
            [
                'itemCode' => 'EG-675170532-102',
                'codeName' => 'Food from Lebanese cuisine',
                'codeNameAr' => 'ماكولات من المطبخ اللبناني',
                'description' => '',
                'descriptionAr' => '',
                'parentCode' => '10006294',
                'activeFrom' => Carbon::parse('2025-04-07'),
            ],
        ];

        foreach ($items as $item) {
            DB::table('item_codes')->insert([
                'codeType' => 'EGS',
                'parentCode' => $item['parentCode'],
                'itemCode' => $item['itemCode'],
                'codeName' => $item['codeName'],
                'codeNameAr' => $item['codeNameAr'],
                'activeFrom' => $item['activeFrom'], // Active from date updated
                'activeTo' => null, // No active to date set
                'description' => $item['description'],
                'descriptionAr' => $item['descriptionAr'],
                'requestReason' => 'This is a unique reason for: ' . $item['itemCode'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
