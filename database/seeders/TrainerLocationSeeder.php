<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TrainerLocation;
use App\Models\TrainerProfile;
use Illuminate\Support\Str;

class TrainerLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trainerProfiles = TrainerProfile::all();
        
        $locationTypes = ['gym', 'outdoor', 'home', 'client_location', 'online'];
        
        // Pakistani cities with coordinates
        $cities = [
            'Karachi' => [
                'coords' => [24.8607, 67.0011],
                'areas' => ['DHA', 'Clifton', 'Gulshan-e-Iqbal', 'North Nazimabad', 'Korangi', 'Malir', 'Saddar', 'PECHS'],
            ],
            'Lahore' => [
                'coords' => [31.5204, 74.3587],
                'areas' => ['Gulberg', 'DHA', 'Cantt', 'Model Town', 'Johar Town', 'Garden Town', 'Faisal Town', 'Iqbal Town'],
            ],
            'Islamabad' => [
                'coords' => [33.6844, 73.0479],
                'areas' => ['F-6', 'F-7', 'F-8', 'F-10', 'F-11', 'G-6', 'G-7', 'Blue Area'],
            ],
            'Rawalpindi' => [
                'coords' => [33.5651, 73.0169],
                'areas' => ['Saddar', 'Commercial Market', 'Cantt', 'Bahria Town', 'DHA', 'Satellite Town'],
            ],
            'Faisalabad' => [
                'coords' => [31.4504, 73.1350],
                'areas' => ['Civil Lines', 'Peoples Colony', 'Gulberg', 'Samanabad', 'Madina Town', 'Model Town'],
            ],
            'Multan' => [
                'coords' => [30.1575, 71.5249],
                'areas' => ['Cantt', 'Gulgasht Colony', 'New Multan', 'Shah Rukn-e-Alam Colony', 'Bosan Road'],
            ],
            'Peshawar' => [
                'coords' => [34.0151, 71.5249],
                'areas' => ['University Town', 'Hayatabad', 'Cantt', 'Saddar', 'Ring Road', 'Board Bazaar'],
            ],
            'Quetta' => [
                'coords' => [30.1798, 66.9750],
                'areas' => ['Cantt', 'Satellite Town', 'Jinnah Town', 'Brewery Road', 'Samungli Road'],
            ],
        ];
        
        // Gym and facility names
        $gymNames = [
            'Fitness First', 'Gold\'s Gym', 'World Gym', 'Anytime Fitness', 'Planet Fitness',
            'Sports Club Pakistan', 'Elite Fitness Center', 'Power Gym', 'Body Building Gym',
            'Cardio Plus', 'Muscle Factory', 'Iron Paradise', 'Fitness Zone', 'Athletic Club',
            'Shape Up Gym', 'Strength & Conditioning Center', 'Ultimate Fitness', 'Pro Gym',
        ];
        
        $outdoorLocations = [
            'Central Park', 'Sports Complex', 'Stadium Grounds', 'Community Center',
            'Public Playground', 'Beach Area', 'Running Track', 'Tennis Courts',
            'Basketball Courts', 'Football Ground', 'Cricket Ground', 'Swimming Pool',
        ];

        foreach ($trainerProfiles as $trainer) {
            // Each trainer has 1-3 locations
            $locationCount = rand(1, 3);
            
            for ($i = 0; $i < $locationCount; $i++) {
                $cityName = array_rand($cities);
                $cityData = $cities[$cityName];
                $area = $cityData['areas'][array_rand($cityData['areas'])];
                
                // Add some variation to coordinates (within ~5km radius)
                $lat = $cityData['coords'][0] + (rand(-50, 50) / 1000); // ±0.05 degrees ≈ ±5km
                $lng = $cityData['coords'][1] + (rand(-50, 50) / 1000);
                
                $locationType = $locationTypes[array_rand($locationTypes)];
                
                // Generate location name based on type
                switch ($locationType) {
                    case 'gym':
                        $locationName = $gymNames[array_rand($gymNames)] . ' - ' . $area;
                        $address = $gymNames[array_rand($gymNames)] . ', ' . $area . ', ' . $cityName;
                        break;
                    case 'outdoor':
                        $locationName = $outdoorLocations[array_rand($outdoorLocations)] . ' - ' . $area;
                        $address = $outdoorLocations[array_rand($outdoorLocations)] . ', ' . $area . ', ' . $cityName;
                        break;
                    case 'home':
                        $locationName = 'Home Studio - ' . $area;
                        $address = 'Private Residence, ' . $area . ', ' . $cityName;
                        break;
                    case 'client_location':
                        $locationName = 'Client Location - ' . $area;
                        $address = 'Various Locations in ' . $area . ', ' . $cityName;
                        break;
                    case 'online':
                        $locationName = 'Online Training Sessions';
                        $address = 'Virtual Location - Anywhere';
                        $lat = null;
                        $lng = null;
                        break;
                }
                
                TrainerLocation::create([
                    'id' => (string) Str::uuid(),
                    'trainer_profile_id' => $trainer->id,
                    'location_name' => $locationName,
                    'location_type' => $locationType,
                    'address' => $address,
                    'city' => $cityName,
                    'area' => $area,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'is_primary' => $i === 0, // First location is primary
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        $this->command->info('Created locations for ' . $trainerProfiles->count() . ' trainers');
    }
}
