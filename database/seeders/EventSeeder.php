<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sports = Sport::all();
        $organizers = User::where('user_role', 'owner')->orWhere('user_role', 'admin')->get();

        if ($sports->isEmpty()) {
            return; // No sports available
        }

        $events = [
            [
                'title' => 'Summer Basketball Tournament',
                'description' => 'Annual summer basketball tournament for all skill levels. Join us for an exciting day of competitive basketball!',
                'sport_id' => $sports->where('name', 'Basketball')->first()?->id ?? $sports->first()->id,
                'event_date' => Carbon::now()->addDays(30)->toDateString(),
                'event_time' => Carbon::now()->addDays(30)->setTime(9, 0),
                'end_date' => Carbon::now()->addDays(30)->toDateString(),
                'end_time' => Carbon::now()->addDays(30)->setTime(18, 0),
                'type' => 'tournament',
                'category' => 'intermediate',
                'difficulty' => 'medium',
                'fee' => 25.00,
                'max_participants' => 64,
                'current_participants' => 0,
                'location' => 'Sports Arena Pro',
                'organizer' => $organizers->random()->name ?? 'Sports Club',
                'requirements' => ['Valid ID', 'Sports shoes', 'Water bottle'],
                'prizes' => ['1st Place: $500', '2nd Place: $300', '3rd Place: $200'],
                'is_active' => true,
                'registration_deadline' => Carbon::now()->addDays(25)->setTime(23, 59),
            ],
            [
                'title' => 'Yoga & Meditation Workshop',
                'description' => 'A relaxing workshop combining yoga poses with meditation techniques for stress relief and mindfulness.',
                'sport_id' => $sports->where('name', 'Yoga')->first()?->id ?? $sports->first()->id,
                'event_date' => Carbon::now()->addDays(14)->toDateString(),
                'event_time' => Carbon::now()->addDays(14)->setTime(10, 0),
                'end_date' => Carbon::now()->addDays(14)->toDateString(),
                'end_time' => Carbon::now()->addDays(14)->setTime(16, 0),
                'type' => 'workshop',
                'category' => 'beginner',
                'difficulty' => 'easy',
                'fee' => 45.00,
                'max_participants' => 30,
                'current_participants' => 0,
                'location' => 'Wellness Hub',
                'organizer' => $organizers->random()->name ?? 'Wellness Hub',
                'requirements' => ['Comfortable clothing', 'Yoga mat (optional)', 'Towel'],
                'prizes' => ['Completion certificate', 'Free month membership'],
                'is_active' => true,
                'registration_deadline' => Carbon::now()->addDays(12)->setTime(23, 59),
            ],
            [
                'title' => 'CrossFit Competition',
                'description' => 'Test your limits in this high-intensity CrossFit competition. WODs designed for all fitness levels.',
                'sport_id' => $sports->where('name', 'CrossFit')->first()?->id ?? $sports->first()->id,
                'event_date' => Carbon::now()->addDays(45)->toDateString(),
                'event_time' => Carbon::now()->addDays(45)->setTime(8, 0),
                'end_date' => Carbon::now()->addDays(45)->toDateString(),
                'end_time' => Carbon::now()->addDays(45)->setTime(17, 0),
                'type' => 'competition',
                'category' => 'advanced',
                'difficulty' => 'hard',
                'fee' => 50.00,
                'max_participants' => 100,
                'current_participants' => 0,
                'location' => 'Power Gym',
                'organizer' => $organizers->random()->name ?? 'Power Gym',
                'requirements' => ['CrossFit experience', 'Proper athletic wear', 'Personal water bottle'],
                'prizes' => ['1st Place: $1000', '2nd Place: $500', '3rd Place: $250'],
                'is_active' => true,
                'registration_deadline' => Carbon::now()->addDays(40)->setTime(23, 59),
            ],
            [
                'title' => 'Swimming Lessons for Beginners',
                'description' => 'Learn the basics of swimming in a safe, supportive environment. All levels welcome!',
                'sport_id' => $sports->where('name', 'Swimming')->first()?->id ?? $sports->first()->id,
                'event_date' => Carbon::now()->addDays(7)->toDateString(),
                'event_time' => Carbon::now()->addDays(7)->setTime(14, 0),
                'end_date' => Carbon::now()->addDays(7)->toDateString(),
                'end_time' => Carbon::now()->addDays(7)->setTime(16, 0),
                'type' => 'class',
                'category' => 'beginner',
                'difficulty' => 'easy',
                'fee' => 30.00,
                'max_participants' => 20,
                'current_participants' => 0,
                'location' => 'Urban Athletics',
                'organizer' => $organizers->random()->name ?? 'Urban Athletics',
                'requirements' => ['Swimsuit', 'Towel', 'Goggles'],
                'prizes' => ['Completion certificate'],
                'is_active' => true,
                'registration_deadline' => Carbon::now()->addDays(5)->setTime(23, 59),
            ],
            [
                'title' => 'Marathon Training Workshop',
                'description' => 'Comprehensive workshop covering marathon training techniques, nutrition, and mental preparation.',
                'sport_id' => $sports->where('name', 'Running')->first()?->id ?? $sports->first()->id,
                'event_date' => Carbon::now()->addDays(21)->toDateString(),
                'event_time' => Carbon::now()->addDays(21)->setTime(9, 0),
                'end_date' => Carbon::now()->addDays(21)->toDateString(),
                'end_time' => Carbon::now()->addDays(21)->setTime(17, 0),
                'type' => 'workshop',
                'category' => 'intermediate',
                'difficulty' => 'medium',
                'fee' => 60.00,
                'max_participants' => 50,
                'current_participants' => 0,
                'location' => 'Elite Fitness Center',
                'organizer' => $organizers->random()->name ?? 'Elite Fitness Center',
                'requirements' => ['Running shoes', 'Comfortable clothing', 'Notebook'],
                'prizes' => ['Training plan', 'Nutrition guide', 'Free coaching session'],
                'is_active' => true,
                'registration_deadline' => Carbon::now()->addDays(18)->setTime(23, 59),
            ],
        ];

        foreach ($events as $eventData) {
            Event::create($eventData);
        }
    }
}
