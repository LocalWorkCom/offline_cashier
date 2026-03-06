<?php

namespace Database\Seeders;

use App\Models\TypeMessageActive;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeMessageActiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypeMessageActive::insert([
            ['name' => 'notification', 'active'=> true],
            ['name' => 'sms', 'active'=> false],
            ['name' => 'email', 'active'=> false],
        ]);
    }
}
