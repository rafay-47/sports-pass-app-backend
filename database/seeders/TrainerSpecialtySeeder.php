<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TrainerSpecialty;
use App\Models\TrainerProfile;
use Illuminate\Support\Str;

class TrainerSpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trainerProfiles = TrainerProfile::with('sport')->get();
        
        // Sport-specific specialties
        $specialtiesBySport = [
            'Football' => [
                'Goalkeeping Training',
                'Striker Development',
                'Midfield Tactics',
                'Defensive Strategies',
                'Set Piece Specialist',
                'Youth Development',
                'Fitness Conditioning',
                'Technical Skills',
                'Ball Control',
                'Passing Accuracy',
            ],
            'Basketball' => [
                'Shooting Technique',
                'Point Guard Development',
                'Post Play',
                'Defensive Fundamentals',
                'Fast Break Offense',
                'Three-Point Shooting',
                'Ball Handling',
                'Rebounding',
                'Team Strategy',
                'Youth Basketball',
            ],
            'Tennis' => [
                'Serve Technique',
                'Forehand Development',
                'Backhand Training',
                'Net Play',
                'Return of Serve',
                'Mental Game',
                'Junior Tennis',
                'Tournament Preparation',
                'Clay Court Specialist',
                'Grass Court Play',
            ],
            'Cricket' => [
                'Batting Technique',
                'Bowling Analysis',
                'Wicket Keeping',
                'Fielding Skills',
                'Power Hitting',
                'Spin Bowling',
                'Fast Bowling',
                'Match Strategy',
                'Youth Cricket',
                'T20 Specialist',
            ],
            'Swimming' => [
                'Freestyle Technique',
                'Butterfly Stroke',
                'Backstroke',
                'Breaststroke',
                'Competitive Swimming',
                'Open Water Swimming',
                'Swimming for Beginners',
                'Endurance Training',
                'Sprint Training',
                'Diving',
            ],
            'Boxing' => [
                'Jab Technique',
                'Footwork',
                'Defense',
                'Combination Punching',
                'Sparring',
                'Conditioning',
                'Mental Preparation',
                'Weight Management',
                'Competition Prep',
                'Amateur Boxing',
            ],
            'Badminton' => [
                'Smash Technique',
                'Drop Shot',
                'Clear Shot',
                'Net Play',
                'Doubles Strategy',
                'Singles Play',
                'Footwork',
                'Racket Grip',
                'Serve Variation',
                'Tournament Play',
            ],
            'Table Tennis' => [
                'Forehand Loop',
                'Backhand Drive',
                'Serve Variation',
                'Return Technique',
                'Spin Control',
                'Speed Play',
                'Defensive Play',
                'Attack Strategy',
                'Equipment Selection',
                'Competition Tactics',
            ],
            'Volleyball' => [
                'Spiking',
                'Setting',
                'Blocking',
                'Serving',
                'Passing',
                'Libero Training',
                'Team Rotation',
                'Beach Volleyball',
                'Indoor Volleyball',
                'Youth Volleyball',
            ],
            'Hockey' => [
                'Stick Handling',
                'Shooting',
                'Passing',
                'Defensive Play',
                'Goalkeeping',
                'Power Play',
                'Penalty Kill',
                'Face-offs',
                'Team Strategy',
                'Conditioning',
            ],
            'Squash' => [
                'Straight Drive',
                'Cross Court',
                'Drop Shot',
                'Boast',
                'Volley',
                'Court Movement',
                'Fitness Training',
                'Mental Game',
                'Match Strategy',
                'Equipment Guide',
            ],
            'Weightlifting' => [
                'Olympic Lifts',
                'Powerlifting',
                'Bodybuilding',
                'Functional Training',
                'Strength Building',
                'Muscle Hypertrophy',
                'Competition Prep',
                'Form Correction',
                'Program Design',
                'Injury Prevention',
            ],
        ];
        
        // General specialties for any sport
        $generalSpecialties = [
            'Youth Training',
            'Adult Fitness',
            'Senior Training',
            'Beginner Friendly',
            'Advanced Training',
            'Competition Preparation',
            'Injury Rehabilitation',
            'Strength Training',
            'Endurance Training',
            'Flexibility Training',
            'Mental Coaching',
            'Nutrition Guidance',
            'Weight Management',
            'Performance Analysis',
            'Team Building',
        ];

        foreach ($trainerProfiles as $trainer) {
            $sportName = $trainer->sport->name ?? 'General';
            $sportSpecialties = $specialtiesBySport[$sportName] ?? [];
            
            // Combine sport-specific and general specialties
            $availableSpecialties = array_merge($sportSpecialties, $generalSpecialties);
            
            // Each trainer gets 2-6 specialties
            $specialtyCount = rand(2, 6);
            $usedSpecialties = [];
            
            for ($i = 0; $i < $specialtyCount; $i++) {
                // Avoid duplicate specialties for the same trainer
                do {
                    $specialty = $availableSpecialties[array_rand($availableSpecialties)];
                } while (in_array($specialty, $usedSpecialties));
                
                $usedSpecialties[] = $specialty;
                
                TrainerSpecialty::create([
                    'id' => (string) Str::uuid(),
                    'trainer_profile_id' => $trainer->id,
                    'specialty' => $specialty,
                    'created_at' => now(),
                ]);
            }
        }
        
        $this->command->info('Created specialties for ' . $trainerProfiles->count() . ' trainers');
    }
}
