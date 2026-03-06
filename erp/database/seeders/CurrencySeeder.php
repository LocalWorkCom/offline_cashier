<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;
use App\Models\Country;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'currency_ar' => 'ريال سعودي',
                'currency_en' => 'Saudi Riyal',
                'currency_symbol' => '﷼',
                'currency_code' => 'SAR',
                'is_default' => 0,
                'country_name' => 'Saudi Arabia',
            ],
            [
                'currency_ar' => 'جنيه مصري',
                'currency_en' => 'Egyptian Pound',
                'currency_symbol' => '£',
                'currency_code' => 'EGP',
                'is_default' => 1,
                'country_name' => 'Egypt',
            ],
            [
                'currency_ar' => 'دينار كويتي',
                'currency_en' => 'Kuwaiti Dinar',
                'currency_symbol' => 'د.ك',
                'currency_code' => 'KWD',
                'is_default' => 0,
                'country_name' => 'Kuwait',
            ],
            [
                'currency_ar' => 'دولار امريكي',
                'currency_en' => 'US Dollar',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'is_default' => 0,
                'country_name' => 'United States',
            ],
        ];

        foreach ($currencies as $currency) {
            $country = Country::where('name_en', $currency['country_name'])->first();

            if ($country) {
                Currency::create([
                    'currency_ar' => $currency['currency_ar'],
                    'currency_en' => $currency['currency_en'],
                    'currency_symbol' => $currency['currency_symbol'],
                    'currency_code' => $currency['currency_code'],
                    'is_default' => $currency['is_default'],
                    'country_id' => $country->id,
                    'created_by' => null,
                    'modified_by' => null,
                    'deleted_by' => null,
                ]);
            }
        }
    }
}
