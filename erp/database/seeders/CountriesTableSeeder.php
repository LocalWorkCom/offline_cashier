<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CountriesTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::beginTransaction();

        // Truncate tables
        DB::table('areas')->truncate();
        DB::table('cities')->truncate();
        DB::table('countries')->truncate();

        $now = Carbon::now();

        // Seed countries
        $countries = [
            [
                'id' => (string) Str::uuid(),
                'name_ar' => 'الكويت',
                'name_en' => 'Kuwait',
                'code' => 'KW',
                'currency_ar' => 'دينار كويتي',
                'currency_en' => 'Kuwaiti Dinar',
                'currency_code' => 'KWD',
                'currency_symbol' => 'د.ك',
                'phone_code' => '+965',
                'length' => 8,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
                'created_by' => 1,
                'deleted_by' => null,
                'modified_by' => null,
                'job_years' => 1,
                'order' => 6,

                'flag' => 'flags/kw.png',
            ],
            [
                'id' => (string) Str::uuid(),
                'name_ar' => 'مصر',
                'name_en' => 'Egypt',
                'code' => 'EG',
                'currency_ar' => 'جنيه مصري',
                'currency_en' => 'Egyptian Pound',
                'currency_code' => 'EGP',
                'currency_symbol' => '£',
                'phone_code' => '+20',
                'length' => 11,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
                'created_by' => 1,
                'deleted_by' => null,
                'modified_by' => null,
                'job_years' => 1,
                'order' => 1,
                'flag' => 'flags/eg.png',
            ],
            [
                'id' => (string) Str::uuid(),
                'name_ar' => 'السعودية',
                'name_en' => 'Saudi Arabia',
                'code' => 'SA',
                'currency_ar' => 'ريال سعودي',
                'currency_en' => 'Saudi Riyal',
                'currency_code' => 'SAR',
                'currency_symbol' => '﷼',
                'phone_code' => '+966',
                'length' => 9,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
                'created_by' => 1,
                'deleted_by' => null,
                'modified_by' => null,
                'job_years' => 1,
                'order' => 7,

                'flag' => 'flags/sa.png',
            ],
            [
                'id' => (string) Str::uuid(),
                'name_ar' => 'الاردن',
                'name_en' => 'Jordan',
                'code' => 'JO',
                'currency_ar' => 'دينار أردني',
                'currency_en' => 'Jordanian Dinar',
                'currency_code' => 'JOD',
                'currency_symbol' => 'د.ا',
                'phone_code' => '+962',
                'length' => 9,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
                'created_by' => 1,
                'deleted_by' => null,
                'modified_by' => null,
                'job_years' => 1,
                'order' => 2,
                'flag' => 'flags/jo.png',
            ],
            [
                'id' => (string) Str::uuid(),
                'name_ar' => 'العراق',
                'name_en' => 'Iraq',
                'code' => 'IQ',
                'currency_ar' => 'دينار عراقي',
                'currency_en' => 'Iraqi Dinar',
                'currency_code' => 'IQD',
                'currency_symbol' =>  'ع.د',
                'phone_code' => '+964',
                'length' => 10,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
                'created_by' => 1,
                'deleted_by' => null,
                'modified_by' => null,
                'job_years' => 1,
                'order' => 3,
                'flag' => 'flags/jo.png',
            ],
            [
                'id' => (string) Str::uuid(),
                'name_ar' => 'المغرب',
                'name_en' => 'Morocco',
                'code' => 'MA',
                'currency_ar' => 'درهم مغربي',
                'currency_en' => 'Moroccan Dirham',
                'currency_code' => 'MAD',
                'currency_symbol' =>  'د.م',
                'phone_code' => '+212',
                'length' => 9,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
                'created_by' => 1,
                'deleted_by' => null,
                'modified_by' => null,
                'job_years' => 1,
                'order' => 4,
                'flag' => 'flags/jo.png',
            ],
            [
                'id' => (string) Str::uuid(),
                'name_ar' => 'سوريا',
                'name_en' => 'Syria',
                'code' => 'SY',
                'currency_ar' => 'ليرة سورية',
                'currency_en' => 'Syrian Pound',
                'currency_code' => 'SYP',
                'currency_symbol' => 'ل.س',
                'phone_code' => '+963',
                'length' => 9,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
                'created_by' => 1,
                'deleted_by' => null,
                'modified_by' => null,
                'job_years' => 1,
                'order' => 5,
                'flag' => 'flags/jo.png',
            ],
        ];

        DB::table('countries')->insert($countries);

        // Create mapping of country name => id
        $countryIds = DB::table('countries')->pluck('id', 'name_en');

        // Seed cities
        $cities = [
            [
                'name_ar' => 'مدينة الكويت',
                'name_en' => 'Kuwait City',
                'country_id' => $countryIds['Kuwait'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_ar' => 'الأحمدي',
                'name_en' => 'Ahmadi',
                'country_id' => $countryIds['Kuwait'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_ar' => 'القاهرة',
                'name_en' => 'Cairo',
                'country_id' => $countryIds['Egypt'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_ar' => 'الجيزة',
                'name_en' => 'Giza',
                'country_id' => $countryIds['Egypt'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_ar' => 'الرياض',
                'name_en' => 'Riyadh',
                'country_id' => $countryIds['Saudi Arabia'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_ar' => 'جدة',
                'name_en' => 'Jeddah',
                'country_id' => $countryIds['Saudi Arabia'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('cities')->insert($cities);

        // Create mapping of city name => id
        $cityIds = DB::table('cities')->pluck('id', 'name_en');

        // Seed areas
        $areas = [
            [
                'name_ar' => 'المرقاب',
                'name_en' => 'Murgab',
                'city_id' => $cityIds['Kuwait City'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_ar' => 'السالمية',
                'name_en' => 'Salmiya',
                'city_id' => $cityIds['Kuwait City'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_ar' => 'الفحيحيل',
                'name_en' => 'Fahaheel',
                'city_id' => $cityIds['Ahmadi'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_ar' => 'المعادي',
                'name_en' => 'Maadi',
                'city_id' => $cityIds['Cairo'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_ar' => 'الهرم',
                'name_en' => 'Haram',
                'city_id' => $cityIds['Giza'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_ar' => 'النسيم',
                'name_en' => 'Al Naseem',
                'city_id' => $cityIds['Riyadh'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_ar' => 'الصفا',
                'name_en' => 'Al Safa',
                'city_id' => $cityIds['Jeddah'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('areas')->insert($areas);
    }
}
