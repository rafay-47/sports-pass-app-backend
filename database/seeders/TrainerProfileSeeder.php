<?php

namespace Database\Seeders;

use App\Models\TrainerProfile;
use App\Models\TrainerCertification;
use App\Models\TrainerSpecialty;
use App\Models\TrainerAvailability;
use App\Models\TrainerLocation;
use App\Models\User;
use App\Models\Sport;
use App\Models\Tier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrainerProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have sports and tiers first
        $this->ensureBasicDataExists();
        
        // Ensure we have users, sports, and tiers to work with
        $trainers = User::where('is_trainer', true)->get();
        $sports = Sport::where('is_active', true)->get();
        $tiers = Tier::where('is_active', true)->get();

        if ($trainers->isEmpty() || $sports->isEmpty() || $tiers->isEmpty()) {
            $this->command->warn('Not enough trainer users, sports, or tiers found. Creating some...');
            
            // Create trainer users if none exist
            if ($trainers->isEmpty()) {
                $trainerUsers = User::factory(15)->create([
                    'is_trainer' => true,
                    'user_role' => 'member'  // Trainers are members with is_trainer = true
                ]);
                $trainers = $trainerUsers;
            }
        }

        $this->command->info('Creating trainer profiles...');

        // Create one trainer profile per trainer user (random sport and tier)
        foreach ($trainers->take(20) as $trainer) {
            // Skip if this user already has a TrainerProfile
            if (TrainerProfile::where('user_id', $trainer->id)->exists()) {
                continue;
            }

            // Pick a random sport and a matching tier for this trainer
            $sport = $sports->random();
            $sportTiers = $tiers->where('sport_id', $sport->id);
            
            // If no tiers exist for this sport, skip this trainer
            if ($sportTiers->isEmpty()) {
                $this->command->warn("No tiers found for sport: {$sport->name}. Skipping trainer: {$trainer->name}");
                continue;
            }
            
            $tier = $sportTiers->random();

            // Create trainer profile with appropriate experience level distribution
            $experienceLevel = fake()->randomElement(['beginner', 'intermediate', 'senior', 'expert']);
            $factory = TrainerProfile::factory();

            // Apply experience level
            switch ($experienceLevel) {
                case 'beginner':
                    $factory = $factory->beginner();
                    break;
                case 'intermediate':
                    $factory = $factory->intermediate();
                    break;
                case 'senior':
                    $factory = $factory->senior();
                    break;
                case 'expert':
                    $factory = $factory->expert();
                    break;
            }

            // 80% chance of being verified
            if (fake()->boolean(80)) {
                $factory = $factory->verified();
            }

            // 90% chance of being available
            if (fake()->boolean(90)) {
                $factory = $factory->available();
            }

            // 20% chance of being a high earner
            if (fake()->boolean(20)) {
                $factory = $factory->highEarner();
            }

            $factory->create([
                'user_id' => $trainer->id,
                'sport_id' => $sport->id,
                'tier_id' => $tier->id,
            ]);
        }

        // Create some specific trainer profiles for testing
        $this->createSpecificTrainerProfiles();

        $this->command->info('Trainer profiles seeded successfully!');
    }

    /**
     * Create specific trainer profiles for testing purposes.
     */
    private function createSpecificTrainerProfiles(): void
    {
        $sports = Sport::where('is_active', true)->get();
        $tiers = Tier::where('is_active', true)->get();

        if ($sports->isEmpty() || $tiers->isEmpty()) {
            return;
        }


        // Create a top-rated expert trainer only if not exists
        $expertTrainer = User::where('email', 'ahmed.expert@sportsclub.pk')->first();
        if (!$expertTrainer) {
            $expertTrainer = User::factory()->create([
                'name' => 'Muhammad Ahmed',
                'email' => 'ahmed.expert@sportsclub.pk',
                'is_trainer' => true,
                'user_role' => 'member'
            ]);
        }

        // Skip if trainer profile already exists
        if (!TrainerProfile::where('user_id', $expertTrainer->id)->exists()) {
            $firstSport = $sports->first();
            $premiumTier = $tiers->where('sport_id', $firstSport->id)->where('tier_name', 'premium')->first();
            if (!$premiumTier) {
                $premiumTier = $tiers->where('sport_id', $firstSport->id)->first();
            }

            if ($premiumTier) {
                TrainerProfile::factory()->expert()->verified()->available()->highEarner()->create([
                    'user_id' => $expertTrainer->id,
                    'sport_id' => $firstSport->id,
                    'tier_id' => $premiumTier->id,
                    'bio' => 'Expert trainer with 15+ years of experience. Specialized in advanced training techniques and has trained national level athletes.',
                    'rating' => 4.9,
                    'experience_years' => 15,
                    'total_sessions' => 450,
                    'total_earnings' => 54000.00,
                    'monthly_earnings' => 8500.00,
                ]);
            }
        }

        // Create a female trainer for female clients
        $femaleTrainer = User::where('email', 'fatima.trainer@sportsclub.pk')->first();
        if (!$femaleTrainer) {
            $femaleTrainer = User::factory()->create([
                'name' => 'Fatima Khan',
                'email' => 'fatima.trainer@sportsclub.pk',
                'gender' => 'female',
                'is_trainer' => true,
                'user_role' => 'member'
            ]);
        }

        // Skip if trainer profile already exists
        if (!TrainerProfile::where('user_id', $femaleTrainer->id)->exists()) {
            $randomSport = $sports->random();
            $sportTiers = $tiers->where('sport_id', $randomSport->id);
            
            if ($sportTiers->isNotEmpty()) {
                TrainerProfile::factory()->intermediate()->verified()->available()->femaleClients()->create([
                    'user_id' => $femaleTrainer->id,
                    'sport_id' => $randomSport->id,
                    'tier_id' => $sportTiers->random()->id,
                    'bio' => 'Certified female trainer specializing in women\'s fitness and health. Creating a comfortable and supportive environment for female clients.',
                    'gender_preference' => 'female',
                    'rating' => 4.6,
                    'experience_years' => 4,
                ]);
            }
        }

        // Create a beginner trainer
        $beginnerTrainer = User::where('email', 'ali.beginner@sportsclub.pk')->first();
        if (!$beginnerTrainer) {
            $beginnerTrainer = User::factory()->create([
                'name' => 'Ali Hassan',
                'email' => 'ali.beginner@sportsclub.pk',
                'is_trainer' => true,
                'user_role' => 'member'
            ]);
        }

        // Skip if trainer profile already exists
        if (!TrainerProfile::where('user_id', $beginnerTrainer->id)->exists()) {
            $randomSport = $sports->random();
            $basicTier = $tiers->where('sport_id', $randomSport->id)->where('tier_name', 'basic')->first();
            if (!$basicTier) {
                $basicTier = $tiers->where('sport_id', $randomSport->id)->first();
            }

            if ($basicTier) {
                TrainerProfile::factory()->beginner()->verified()->available()->create([
                    'user_id' => $beginnerTrainer->id,
                    'sport_id' => $randomSport->id,
                    'tier_id' => $basicTier->id,
                    'bio' => 'Enthusiastic new trainer ready to help you achieve your fitness goals. Fresh perspective with latest training methodologies.',
                    'rating' => 4.1,
                    'experience_years' => 1,
                ]);
            }
        }

        // Create an unverified trainer
        $unverifiedTrainer = User::where('email', 'omar.unverified@sportsclub.pk')->first();
        if (!$unverifiedTrainer) {
            $unverifiedTrainer = User::factory()->create([
                'name' => 'Omar Malik',
                'email' => 'omar.unverified@sportsclub.pk',
                'is_trainer' => true,
                'user_role' => 'member'
            ]);
        }

        // Skip if trainer profile already exists
        if (!TrainerProfile::where('user_id', $unverifiedTrainer->id)->exists()) {
            $randomSport = $sports->random();
            $sportTiers = $tiers->where('sport_id', $randomSport->id);
            
            if ($sportTiers->isNotEmpty()) {
                TrainerProfile::factory()->unverified()->available()->create([
                    'user_id' => $unverifiedTrainer->id,
                    'sport_id' => $randomSport->id,
                    'tier_id' => $sportTiers->random()->id,
                    'bio' => 'Passionate trainer awaiting verification. Eager to start helping clients achieve their fitness goals.',
                    'rating' => 0.0,
                    'experience_years' => 2,
                    'total_sessions' => 0,
                    'total_earnings' => 0.00,
                    'monthly_earnings' => 0.00,
                ]);
            }
        }

        $this->command->info('Specific trainer profiles created for testing!');
        
        // Create related data for trainer profiles
        $this->createTrainerRelatedData();
    }

    /**
     * Create certifications, specialties, availability, and locations for trainers.
     */
    private function createTrainerRelatedData(): void
    {
        $this->command->info('Creating trainer certifications, specialties, availability, and locations...');

        $trainerProfiles = TrainerProfile::with('user')->get();

        foreach ($trainerProfiles as $profile) {
            // Create certifications
            $this->createCertifications($profile);
            
            // Create specialties
            $this->createSpecialties($profile);
            
            // Create availability
            $this->createAvailability($profile);
            
            // Create locations
            $this->createLocations($profile);
        }

        $this->command->info('Trainer related data created successfully!');
    }

    /**
     * Create certifications for a trainer profile.
     */
    private function createCertifications(TrainerProfile $profile): void
    {
        $certifications = [
            ['name' => 'Certified Personal Trainer', 'org' => 'NASM'],
            ['name' => 'Strength and Conditioning Specialist', 'org' => 'NSCA'],
            ['name' => 'Corrective Exercise Specialist', 'org' => 'NASM'],
            ['name' => 'Nutrition Coach Certification', 'org' => 'Precision Nutrition'],
            ['name' => 'Group Fitness Instructor', 'org' => 'ACE'],
            ['name' => 'Youth Exercise Specialist', 'org' => 'NASM'],
            ['name' => 'Senior Fitness Specialist', 'org' => 'ACE'],
            ['name' => 'Functional Movement Screen', 'org' => 'FMS'],
        ];

        $numCerts = fake()->numberBetween(1, 4);
        $selectedCerts = fake()->randomElements($certifications, $numCerts);

        foreach ($selectedCerts as $cert) {
            TrainerCertification::create([
                'trainer_profile_id' => $profile->id,
                'certification_name' => $cert['name'],
                'issuing_organization' => $cert['org'],
                'issue_date' => fake()->dateTimeBetween('-5 years', '-1 year')->format('Y-m-d'),
                'expiry_date' => fake()->dateTimeBetween('+1 year', '+3 years')->format('Y-m-d'),
                'is_verified' => fake()->boolean(80),
            ]);
        }
    }

    /**
     * Create specialties for a trainer profile.
     */
    private function createSpecialties(TrainerProfile $profile): void
    {
        $specialties = [
            'Weight Loss',
            'Muscle Building',
            'Strength Training',
            'Cardio Training',
            'Functional Training',
            'HIIT Training',
            'CrossFit',
            'Powerlifting',
            'Bodybuilding',
            'Athletic Performance',
            'Injury Rehabilitation',
            'Flexibility and Mobility',
            'Yoga',
            'Pilates',
            'Sports-Specific Training',
            'Senior Fitness',
            'Youth Training',
            'Prenatal Fitness',
            'Posture Correction',
            'Nutrition Coaching',
        ];

        $numSpecialties = fake()->numberBetween(2, 6);
        $selectedSpecialties = fake()->randomElements($specialties, $numSpecialties);

        foreach ($selectedSpecialties as $specialty) {
            TrainerSpecialty::create([
                'trainer_profile_id' => $profile->id,
                'specialty' => $specialty,
            ]);
        }
    }

    /**
     * Create availability for a trainer profile.
     */
    private function createAvailability(TrainerProfile $profile): void
    {
        // Create availability for random days of the week
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $availableDays = fake()->randomElements($daysOfWeek, fake()->numberBetween(3, 6));

        foreach ($availableDays as $dayName) {
            $startTime = fake()->randomElement(['06:00', '07:00', '08:00', '09:00']);
            $endTime = fake()->randomElement(['17:00', '18:00', '19:00', '20:00', '21:00']);

            TrainerAvailability::updateOrCreate(
                [
                    'trainer_profile_id' => $profile->id,
                    'day_of_week' => $dayName,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ],
                [
                    'is_available' => fake()->boolean(90),
                ]
            );
        }
    }

    /**
     * Create locations for a trainer profile.
     */
    private function createLocations(TrainerProfile $profile): void
    {
        $locations = [
            ['name' => 'DHA Gym Center', 'address' => 'Phase 4, DHA, Lahore', 'city' => 'Lahore', 'lat' => 31.4697, 'lng' => 74.4028],
            ['name' => 'Gulberg Fitness Club', 'address' => 'Gulberg III, Lahore', 'city' => 'Lahore', 'lat' => 31.5203, 'lng' => 74.3587],
            ['name' => 'Model Town Sports Complex', 'address' => 'Model Town, Lahore', 'city' => 'Lahore', 'lat' => 31.5659, 'lng' => 74.3199],
            ['name' => 'Johar Town Gym', 'address' => 'Johar Town, Lahore', 'city' => 'Lahore', 'lat' => 31.4760, 'lng' => 74.2663],
            ['name' => 'Canton Fitness Center', 'address' => 'Cantonment, Lahore', 'city' => 'Lahore', 'lat' => 31.5470, 'lng' => 74.3436],
            ['name' => 'Home Training', 'address' => 'Client Location', 'city' => 'Lahore', 'lat' => null, 'lng' => null],
            ['name' => 'Online Sessions', 'address' => 'Virtual Training', 'city' => 'Lahore', 'lat' => null, 'lng' => null],
        ];

        $numLocations = fake()->numberBetween(1, 3);
        $selectedLocations = fake()->randomElements($locations, $numLocations);

        foreach ($selectedLocations as $index => $location) {
            TrainerLocation::create([
                'trainer_profile_id' => $profile->id,
                'location_name' => $location['name'],
                'address' => $location['address'],
                'city' => $location['city'],
                'latitude' => $location['lat'],
                'longitude' => $location['lng'],
                'is_primary' => $index === 0, // First location is primary
            ]);
        }
    }

    /**
     * Ensure we have basic sports and tiers data before creating trainer profiles.
     */
    private function ensureBasicDataExists(): void
    {
        $sports = Sport::where('is_active', true)->get();
        $tiers = Tier::where('is_active', true)->get();

        if ($sports->isEmpty()) {
            $this->command->warn('No sports found. Please run SportSeeder first.');
            $this->call(SportSeeder::class);
        }

        if ($tiers->isEmpty()) {
            $this->command->warn('No tiers found. Please run TierSeeder first.');
            $this->call(TierSeeder::class);
        }

        // Verify each sport has at least one tier
        $sports = Sport::where('is_active', true)->get();
        foreach ($sports as $sport) {
            $sportTiers = Tier::where('sport_id', $sport->id)->where('is_active', true)->count();
            if ($sportTiers === 0) {
                $this->command->warn("Sport '{$sport->name}' has no tiers. Creating basic tier...");
                
                // Create a basic tier for this sport
                Tier::create([
                    'sport_id' => $sport->id,
                    'tier_name' => 'basic',
                    'display_name' => 'Basic Plan',
                    'description' => 'Basic membership tier',
                    'price' => 2000.00,
                    'duration_days' => 365,
                    'is_active' => true,
                ]);
            }
        }
    }
}
