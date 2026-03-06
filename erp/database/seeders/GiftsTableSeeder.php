<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GiftsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gifts = [
            ['name' => 'Welcome Gift', 'expiration_date' => '2025-10-30', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('gifts')->insert($gifts);
    }
}
