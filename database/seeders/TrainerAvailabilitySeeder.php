<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TrainerAvailability;
use App\Models\TrainerProfile;
use Illuminate\Support\Str;

class TrainerAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trainerProfiles = TrainerProfile::all();
        
        // Days of week (as strings to match database constraint)
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        // Common time slots
        $timeSlots = [
            ['06:00', '08:00'], // Early morning
            ['08:00', '10:00'], // Morning
            ['10:00', '12:00'], // Late morning
            ['12:00', '14:00'], // Noon
            ['14:00', '16:00'], // Afternoon
            ['16:00', '18:00'], // Late afternoon
            ['18:00', '20:00'], // Evening
            ['20:00', '22:00'], // Night
        ];

        foreach ($trainerProfiles as $trainer) {
            // Each trainer is available 3-6 days per week
            $availableDays = rand(3, 6);
            $shuffledDays = $daysOfWeek;
            shuffle($shuffledDays);
            $selectedDays = array_slice($shuffledDays, 0, $availableDays);
            
            foreach ($selectedDays as $dayName) {
                // Each day can have 1-4 time slots
                $slotsPerDay = rand(1, 4);
                $usedSlots = [];
                
                for ($i = 0; $i < $slotsPerDay; $i++) {
                    // Avoid overlapping time slots
                    do {
                        $slot = $timeSlots[array_rand($timeSlots)];
                        $slotKey = $slot[0] . '-' . $slot[1];
                    } while (in_array($slotKey, $usedSlots));
                    
                    $usedSlots[] = $slotKey;
                    
                    TrainerAvailability::create([
                        'id' => (string) Str::uuid(),
                        'trainer_profile_id' => $trainer->id,
                        'day_of_week' => $dayName,
                        'start_time' => $slot[0],
                        'end_time' => $slot[1],
                        'is_available' => fake()->boolean(90), // 90% available
                        'created_at' => now(),
                    ]);
                }
            }
        }
        
        $this->command->info('Created availability schedules for ' . $trainerProfiles->count() . ' trainers');
    }
}
