<?php

namespace Database\Seeders;

use App\Models\CheckIn;
use App\Models\Membership;
use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CheckInSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $memberships = Membership::all();
        $clubs = Club::all();

        if ($memberships->isEmpty() || $clubs->isEmpty()) {
            $this->command->info('No memberships or clubs found. Skipping check-ins.');
            return;
        }

        $sports = ['Basketball', 'Swimming', 'Tennis', 'Football', 'Volleyball', 'Gym', 'Yoga', 'Boxing'];
        $locations = ['Main Entrance', 'Gym Area', 'Pool Deck', 'Court Area', 'Reception', 'Side Door'];

        foreach ($memberships as $membership) {
            // Create 5-15 check-ins per membership over the last 30 days
            $numCheckIns = rand(5, 15);

            for ($i = 0; $i < $numCheckIns; $i++) {
                $checkInDate = Carbon::now()->subDays(rand(0, 30));
                $checkInTime = $checkInDate->copy()->setTime(rand(6, 22), rand(0, 59));
                $duration = rand(30, 180); // 30 minutes to 3 hours
                $checkOutTime = $checkInTime->copy()->addMinutes($duration);

                // Randomly select a club (could be different from membership club)
                $club = $clubs->random();

                CheckIn::create([
                    'user_id' => $membership->user_id,
                    'membership_id' => $membership->id,
                    'club_id' => $club->id,
                    'check_in_date' => $checkInDate->toDateString(),
                    'check_in_time' => $checkInTime,
                    'sport_type' => $sports[array_rand($sports)],
                    'qr_code_used' => (bool) rand(0, 1),
                    'duration_minutes' => $duration,
                    'notes' => $this->getRandomCheckInNote(),
                ]);
            }
        }

        $this->command->info('Check-ins seeded successfully!');
    }

    /**
     * Get a random check-in note.
     */
    private function getRandomCheckInNote(): ?string
    {
        $notes = [
            'Regular workout session',
            'Group training class',
            'Personal training session',
            'Swimming practice',
            'Basketball game',
            'Yoga and meditation',
            'Weight training',
            'Cardio workout',
            'Tennis lesson',
            'Boxing training',
            'Pool time',
            'Gym equipment usage',
            'Group fitness class',
            'Sports practice',
            'Facility tour',
        ];

        return $notes[array_rand($notes)];
    }
}
