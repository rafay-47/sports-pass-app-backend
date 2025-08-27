<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facilities = [
            ['name' => 'Cafeteria', 'description' => 'On-site cafe and refreshments', 'is_active' => true],
            ['name' => 'First Aid', 'description' => 'First aid station and medical assistance', 'is_active' => true],
            ['name' => 'Physio', 'description' => 'Physiotherapy services available', 'is_active' => true],
            ['name' => 'Indoor Courts', 'description' => 'Indoor courts for multiple sports', 'is_active' => true],
        ];

        foreach ($facilities as $f) {
            Facility::updateOrCreate(['name' => $f['name']], $f);
        }

        Facility::factory()->count(8)->create();
    }
}
