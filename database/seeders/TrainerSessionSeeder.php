<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TrainerSession;
use App\Models\TrainerProfile;
use App\Models\User;
use App\Models\Sport;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TrainerSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trainerProfiles = TrainerProfile::with('sport')->get();
        $clientUsers = User::where('user_role', 'member')->get();

        if ($clientUsers->isEmpty()) {
            $this->command->warn('No member users found to create sessions with');
            return;
        }

        // Get memberships for clients
        $memberships = \App\Models\Membership::with('user')->get();
        if ($memberships->isEmpty()) {
            $this->command->warn('No memberships found to create sessions with');
            return;
        }
        
        $sessionStatuses = ['scheduled', 'completed', 'cancelled', 'no_show'];
        $paymentStatuses = ['pending', 'completed', 'failed', 'refunded'];
        
        foreach ($trainerProfiles as $trainer) {
            // Each trainer has 3-15 sessions (mix of past and future)
            $sessionCount = rand(3, 15);
            
            for ($i = 0; $i < $sessionCount; $i++) {
                // Only use memberships that match the trainer's sport
                $matchingMemberships = $memberships->where('sport_id', $trainer->sport_id);
                if ($matchingMemberships->isEmpty()) {
                    continue; // skip if no matching membership
                }
                $membership = $matchingMemberships->random();
                $clientUser = $membership->user;

                // Generate sessions from 3 months ago to 2 months in future
                $sessionDate = fake()->dateTimeBetween('-3 months', '+2 months');
                $sessionDate = Carbon::parse($sessionDate);

                // Generate session times
                $startHour = rand(6, 20); // 6 AM to 8 PM
                $sessionTime = sprintf('%02d:00', $startHour);
                $durationMinutes = [60, 90, 120, 180][array_rand([60, 90, 120, 180])]; // 1-3 hour sessions

                // Status logic based on date
                $now = Carbon::now();
                if ($sessionDate->isFuture()) {
                    // Future sessions are mostly scheduled
                    $status = fake()->randomElement(['scheduled', 'scheduled', 'scheduled', 'cancelled']);
                    $paymentStatus = 'pending';
                } else {
                    // Past sessions have various statuses
                    $status = fake()->randomElement(['completed', 'completed', 'completed', 'completed', 'no_show', 'cancelled']);
                    $paymentStatus = $status === 'completed' ? 'completed' :
                                   ($status === 'cancelled' ? 'refunded' : 'pending');
                }

                // Session fee based on trainer's hourly rate
                $baseFee = floatval($trainer->hourly_rate ?? 100);
                $sessionDurationHours = $durationMinutes / 60;
                $sessionFee = $baseFee * $sessionDurationHours;

                // Add some variation (±20%)
                $sessionFee = $sessionFee * (0.8 + (rand(0, 40) / 100));

                // Calculate start_time and end_time from session_time and duration_minutes
                $startTime = $sessionTime;
                $endTime = date('H:i', strtotime($startTime) + $durationMinutes * 60);
                $sessionData = [
                    'id' => (string) Str::uuid(),
                    'trainer_profile_id' => $trainer->id,
                    'trainee_user_id' => $clientUser->id,
                    'trainee_membership_id' => $membership->id,
                    'session_date' => $sessionDate->format('Y-m-d'),
                    'session_time' => $sessionTime,
                    'duration_minutes' => $durationMinutes,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => $status,
                    'fee_amount' => round($sessionFee, 2),
                    'payment_status' => $paymentStatus,
                    'location' => $this->generateLocation($trainer),
                    'notes' => $this->generateSessionNotes($status, $trainer->sport->name ?? 'Training'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Add rating and feedback for completed sessions
                if ($status === 'completed') {
                    $sessionData['trainee_rating'] = rand(3, 5); // 3-5 star ratings
                    $sessionData['trainee_feedback'] = $this->generateFeedback($sessionData['trainee_rating']);
                    $sessionData['trainer_notes'] = $this->generateTrainerNotes($sessionData['trainee_rating']);
                }

                TrainerSession::create($sessionData);
            }
        }
        
        $this->command->info('Created sessions for ' . $trainerProfiles->count() . ' trainers');
    }
    
    private function generateLocation($trainer): string
    {
        $locations = [
            "Sports Club Main Gym",
            "Outdoor Training Ground",
            "Basketball Court A",
            "Tennis Court 1",
            "Swimming Pool Complex",
            "Boxing Ring",
            "Football Field",
            "Squash Court 2",
            "Badminton Court 3",
            "Client's Home",
            "Private Studio",
            "Online Session",
        ];
        
        return $locations[array_rand($locations)];
    }
    
    private function generateSessionNotes($status, $sportName): string
    {
        $notes = [
            'scheduled' => [
                "Upcoming {$sportName} training session",
                "Regular weekly {$sportName} practice",
                "Advanced {$sportName} techniques session",
                "Beginner-friendly {$sportName} training",
                "Competition preparation for {$sportName}",
            ],
            'completed' => [
                "Great {$sportName} session! Client showed excellent improvement.",
                "Focused on {$sportName} fundamentals. Good progress made.",
                "Intensive {$sportName} training completed successfully.",
                "Client mastered new {$sportName} techniques today.",
                "Excellent effort in today's {$sportName} session.",
            ],
            'cancelled' => [
                "Session cancelled due to weather conditions",
                "Client had to reschedule due to personal emergency",
                "Trainer was unwell, session rescheduled",
                "Facility was unavailable, session moved to next week",
                "Client cancelled 24 hours in advance",
            ],
            'no_show' => [
                "Client did not show up for scheduled session",
                "No communication from client, marked as no-show",
                "Client failed to attend without prior notice",
            ],
        ];
        
        $statusNotes = $notes[$status] ?? ["Session status: {$status}"];
        return $statusNotes[array_rand($statusNotes)];
    }
    
    private function generateFeedback($rating): string
    {
        $feedbacks = [
            5 => [
                "Excellent trainer! Very knowledgeable and motivating.",
                "Amazing session! Learned so much and had great fun.",
                "Top-notch training! Highly recommend this trainer.",
                "Outstanding coaching! Already seeing improvements.",
                "Fantastic experience! Will definitely book again.",
            ],
            4 => [
                "Great session! Trainer was professional and helpful.",
                "Good training session, learned new techniques.",
                "Solid coaching, would recommend to others.",
                "Enjoyed the session, trainer was patient and skilled.",
                "Good experience overall, will book again.",
            ],
            3 => [
                "Decent session, trainer was okay.",
                "Average training, nothing special but not bad.",
                "Session was fine, met my basic expectations.",
                "Trainer was competent, session was productive.",
                "Good session, though could be more engaging.",
            ],
        ];
        
        $ratingFeedbacks = $feedbacks[$rating] ?? ["Session was rated {$rating} stars"];
        return $ratingFeedbacks[array_rand($ratingFeedbacks)];
    }
    
    private function generateTrainerNotes($rating): string
    {
        $notes = [
            5 => [
                "Excellent progress! Client is very motivated and follows instructions well.",
                "Outstanding session! Client exceeded expectations and showed great improvement.",
                "Fantastic attitude and dedication. Client is ready for advanced techniques.",
                "Remarkable improvement in form and technique. Keep up the excellent work!",
            ],
            4 => [
                "Good session overall. Client is making steady progress.",
                "Client is committed and showing consistent improvement.",
                "Solid performance today. Working on specific areas for improvement.",
                "Good effort from client. Ready to move to next level soon.",
            ],
            3 => [
                "Average session. Client needs to focus more on basic techniques.",
                "Session went okay. Client should practice more between sessions.",
                "Need to work on consistency. Client has potential but needs more dedication.",
                "Adequate performance. Recommended additional practice sessions.",
            ],
        ];
        
        $trainerNotes = $notes[$rating] ?? ["Session completed with {$rating} star rating"];
        return $trainerNotes[array_rand($trainerNotes)];
    }
}
