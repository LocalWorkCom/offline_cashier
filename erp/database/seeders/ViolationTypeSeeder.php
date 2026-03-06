<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ViolationType;

class ViolationTypeSeeder extends Seeder
{
    public function run()
    {
        // Inserting predefined violation types into the violation_types table without description
        ViolationType::create([
            'name' => 'Traffic Violations',
        ]);

        ViolationType::create([
            'name' => 'Municipal Violations',
        ]);

        ViolationType::create([
            'name' => 'Fire Safety Violations',
        ]);

        ViolationType::create([
            'name' => 'Labor & Employee Affairs Violations',
        ]);

        ViolationType::create([
            'name' => 'Environmental Violations',
        ]);

        ViolationType::create([
            'name' => 'Tourism Violations',
        ]);

        ViolationType::create([
            'name' => 'Facility Management Violations',
        ]);
    }
}
