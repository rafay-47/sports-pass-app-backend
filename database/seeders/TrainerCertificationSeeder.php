<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TrainerCertification;
use App\Models\TrainerProfile;
use Illuminate\Support\Str;

class TrainerCertificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trainerProfiles = TrainerProfile::all();
        
        // Common certification types for sports trainers
        $certificationTypes = [
            'NASM Certified Personal Trainer',
            'ACE Personal Trainer Certification',
            'ISSA Certified Fitness Trainer',
            'CPR and First Aid Certification',
            'Youth Sports Safety Certification',
            'Strength and Conditioning Specialist',
            'Corrective Exercise Specialist',
            'Sports Nutrition Specialist',
            'Group Fitness Instructor',
            'Functional Movement Screen (FMS)',
            'TRX Suspension Training',
            'Pilates Instructor Certification',
            'Yoga Teacher Training (200hr)',
            'Crossfit Level 1 Trainer',
            'Olympic Weightlifting Coach',
            'Swimming Instructor Certification',
            'Tennis Teaching Professional',
            'Basketball Coaching License',
            'Football Coaching Certification',
            'Boxing/Martial Arts Instructor',
        ];
        
        $issuingOrganizations = [
            'National Academy of Sports Medicine (NASM)',
            'American Council on Exercise (ACE)',
            'International Sports Sciences Association (ISSA)',
            'National Strength and Conditioning Association (NSCA)',
            'American Red Cross',
            'Pakistan Sports Board',
            'International Tennis Federation (ITF)',
            'FIBA Basketball',
            'FIFA Football',
            'World Boxing Council (WBC)',
            'Pilates Method Alliance',
            'Yoga Alliance',
            'Crossfit Inc.',
            'USA Swimming',
            'National Federation of Professional Trainers',
        ];

        foreach ($trainerProfiles as $trainer) {
            // Each trainer gets 1-4 certifications
            $certificationCount = rand(1, 4);
            $usedCertifications = [];
            
            for ($i = 0; $i < $certificationCount; $i++) {
                // Avoid duplicate certifications for the same trainer
                do {
                    $certification = $certificationTypes[array_rand($certificationTypes)];
                } while (in_array($certification, $usedCertifications));
                
                $usedCertifications[] = $certification;
                
                $issuedDate = fake()->dateTimeBetween('-5 years', '-6 months');
                $expiryDate = fake()->dateTimeBetween('+6 months', '+3 years');
                
                TrainerCertification::create([
                    'id' => (string) Str::uuid(),
                    'trainer_profile_id' => $trainer->id,
                    'certification_name' => $certification,
                    'issuing_organization' => $issuingOrganizations[array_rand($issuingOrganizations)],
                    'issue_date' => $issuedDate,
                    'expiry_date' => $expiryDate,
                    'is_verified' => fake()->boolean(85), // 85% verified
                    'created_at' => now(),
                ]);
            }
        }
        
        $this->command->info('Created certifications for ' . $trainerProfiles->count() . ' trainers');
    }
}
