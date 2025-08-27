<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a handful of common amenities
        $amenities = [
            ['name' => 'WiFi', 'description' => 'High speed internet access', 'is_active' => true],
            ['name' => 'Parking', 'description' => 'On-site parking available', 'is_active' => true],
            ['name' => 'Locker Rooms', 'description' => 'Secure locker rooms with showers', 'is_active' => true],
            ['name' => 'Showers', 'description' => 'Hot water showers available', 'is_active' => true],
            ['name' => 'Sauna', 'description' => 'Relaxing sauna facilities', 'is_active' => true],
        ];

        foreach ($amenities as $a) {
            Amenity::updateOrCreate(['name' => $a['name']], $a);
        }

        // Add some random ones via factory
        Amenity::factory()->count(10)->create();
    }
}
