<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Sport;
use App\Models\Amenity;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data
        $sports = Sport::all();
        $amenities = Amenity::all();
        $facilities = Facility::all();
        $owners = User::where('user_role', 'owner')->get();

        if ($owners->isEmpty()) {
            // Create some club owners if none exist
            $owners = collect();
            for ($i = 0; $i < 5; $i++) {
                $owners->push(User::factory()->create([
                    'user_role' => 'owner',
                    'email' => "club_owner{$i}@example.com",
                ]));
            }
        }

        $clubs = [
            [
                'name' => 'Elite Fitness Center',
                'description' => 'Premium fitness facility with state-of-the-art equipment and professional trainers.',
                'address' => '123 Fitness Street, Downtown',
                'city' => 'New York',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'phone' => '+1-555-0101',
                'email' => 'info@elitefitness.com',
                'rating' => 4.5,
                'category' => 'mixed',
                'qr_code' => 'ELITE_FITNESS_' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'status' => 'active',
                'verification_status' => 'verified',
                'owner_id' => $owners->random()->id,
                'sport_id' => $sports->random()->id,
                'timings' => [
                    'monday' => ['open' => '05:00', 'close' => '23:00'],
                    'tuesday' => ['open' => '05:00', 'close' => '23:00'],
                    'wednesday' => ['open' => '05:00', 'close' => '23:00'],
                    'thursday' => ['open' => '05:00', 'close' => '23:00'],
                    'friday' => ['open' => '05:00', 'close' => '23:00'],
                    'saturday' => ['open' => '06:00', 'close' => '22:00'],
                    'sunday' => ['open' => '08:00', 'close' => '20:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Sports Arena Pro',
                'description' => 'Multi-sport complex featuring basketball, volleyball, and fitness training.',
                'address' => '456 Sports Avenue, Midtown',
                'city' => 'New York',
                'latitude' => 40.7589,
                'longitude' => -73.9851,
                'phone' => '+1-555-0202',
                'email' => 'contact@sportsarena.com',
                'rating' => 4.3,
                'category' => 'mixed',
                'qr_code' => 'SPORTS_ARENA_' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'status' => 'active',
                'verification_status' => 'verified',
                'owner_id' => $owners->random()->id,
                'sport_id' => $sports->random()->id,
                'timings' => [
                    'monday' => ['open' => '06:00', 'close' => '22:00'],
                    'tuesday' => ['open' => '06:00', 'close' => '22:00'],
                    'wednesday' => ['open' => '06:00', 'close' => '22:00'],
                    'thursday' => ['open' => '06:00', 'close' => '22:00'],
                    'friday' => ['open' => '06:00', 'close' => '22:00'],
                    'saturday' => ['open' => '07:00', 'close' => '21:00'],
                    'sunday' => ['open' => '09:00', 'close' => '19:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Wellness Hub',
                'description' => 'Holistic wellness center offering yoga, meditation, and spa services.',
                'address' => '789 Wellness Boulevard, Uptown',
                'city' => 'New York',
                'latitude' => 40.7831,
                'longitude' => -73.9712,
                'phone' => '+1-555-0303',
                'email' => 'hello@wellnesshub.com',
                'rating' => 4.7,
                'category' => 'mixed',
                'qr_code' => 'WELLNESS_HUB_' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'status' => 'active',
                'verification_status' => 'verified',
                'owner_id' => $owners->random()->id,
                'sport_id' => $sports->random()->id,
                'timings' => [
                    'monday' => ['open' => '07:00', 'close' => '21:00'],
                    'tuesday' => ['open' => '07:00', 'close' => '21:00'],
                    'wednesday' => ['open' => '07:00', 'close' => '21:00'],
                    'thursday' => ['open' => '07:00', 'close' => '21:00'],
                    'friday' => ['open' => '07:00', 'close' => '21:00'],
                    'saturday' => ['open' => '08:00', 'close' => '20:00'],
                    'sunday' => ['open' => '10:00', 'close' => '18:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Power Gym',
                'description' => 'High-intensity training facility with modern equipment and expert coaching.',
                'address' => '321 Strength Lane, Brooklyn',
                'city' => 'New York',
                'latitude' => 40.6782,
                'longitude' => -73.9442,
                'phone' => '+1-555-0404',
                'email' => 'team@powergym.com',
                'rating' => 4.2,
                'category' => 'mixed',
                'qr_code' => 'POWER_GYM_' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'status' => 'active',
                'verification_status' => 'verified',
                'owner_id' => $owners->random()->id,
                'sport_id' => $sports->random()->id,
                'timings' => [
                    'monday' => ['open' => '05:30', 'close' => '23:30'],
                    'tuesday' => ['open' => '05:30', 'close' => '23:30'],
                    'wednesday' => ['open' => '05:30', 'close' => '23:30'],
                    'thursday' => ['open' => '05:30', 'close' => '23:30'],
                    'friday' => ['open' => '05:30', 'close' => '23:30'],
                    'saturday' => ['open' => '06:00', 'close' => '22:00'],
                    'sunday' => ['open' => '08:00', 'close' => '20:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Urban Athletics',
                'description' => 'Urban sports complex with indoor and outdoor facilities for various athletic activities.',
                'address' => '654 Athletic Drive, Queens',
                'city' => 'New York',
                'latitude' => 40.7282,
                'longitude' => -73.7949,
                'phone' => '+1-555-0505',
                'email' => 'info@urbanathletics.com',
                'rating' => 4.4,
                'category' => 'mixed',
                'qr_code' => 'URBAN_ATHLETICS_' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'status' => 'active',
                'verification_status' => 'verified',
                'owner_id' => $owners->random()->id,
                'sport_id' => $sports->random()->id,
                'timings' => [
                    'monday' => ['open' => '06:00', 'close' => '22:00'],
                    'tuesday' => ['open' => '06:00', 'close' => '22:00'],
                    'wednesday' => ['open' => '06:00', 'close' => '22:00'],
                    'thursday' => ['open' => '06:00', 'close' => '22:00'],
                    'friday' => ['open' => '06:00', 'close' => '22:00'],
                    'saturday' => ['open' => '07:00', 'close' => '21:00'],
                    'sunday' => ['open' => '09:00', 'close' => '19:00'],
                ],
                'is_active' => true,
            ],
        ];

        foreach ($clubs as $clubData) {
            $club = Club::create($clubData);

            // Attach random amenities (1-3 per club)
            if ($amenities->isNotEmpty()) {
                $randomAmenities = $amenities->random(min(3, $amenities->count()));
                $club->amenities()->attach($randomAmenities->pluck('id'));
            }

            // Attach random facilities (1-3 per club)
            if ($facilities->isNotEmpty()) {
                $randomFacilities = $facilities->random(min(3, $facilities->count()));
                $club->facilities()->attach($randomFacilities->pluck('id'));
            }
        }
    }
}
