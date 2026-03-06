<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryCodesSeeder extends Seeder
{
    public function run()
    {
        $categoryCodes = [
            [
                'item_code' => '10000623',
                'category' => 'Food/Beverage 🢒 Beverages 🢒 Beverages Variety Packs',
                'description' => 'Description of Beverages Variety Packs',
                'description_ar' => 'مجموعات متنوعة من المشروبات',
                'activity' => 'Yes (01/01/2019)', // Activity date or status
            ],
            [
                'item_code' => '10000626',
                'category' => 'Food/Beverage 🢒 Fish and Seafood 🢒 Aquatic Invertebrates/Fish/Shellfish/Seafood Combination',
                'description' => 'Description of Aquatic Invertebrates/Fish/Shellfish/Seafood Mixes - Prepared/Processed (Frozen)',
                'description_ar' => 'تشكيلات من اللافقاريات البحرية/الأسماك/ المحار/ أطعمة البحر - المجهزة/المحضرة (المجمدة)',
                'activity' => 'Yes (01/01/2019)', // Activity date or status
            ],
            [
                'item_code' => '10006294',
                'category' => 'Food/Beverage 🢒 Meat/Poultry/Other Animals 🢒 Meat/Poultry/Other Animals - Prepared/Processed',
                'description' => 'Description of Lamb - Prepared/Processed',
                'description_ar' => 'لحم النعجة - مجهز/معالج',
                'activity' => 'Yes (01/01/2019)', // Activity date or status
            ]
         
            // Add more records here as needed
        ];

        // Insert the data into the category_codes table
        DB::table('category_codes')->insert($categoryCodes);
    }
}
