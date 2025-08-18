<?php

namespace Database\Seeders;

use App\Models\TrainerProfile;
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

        // Create a variety of trainer profiles
        foreach ($trainers->take(20) as $trainer) {
            foreach ($sports->random(fake()->numberBetween(1, 3)) as $sport) {
                // Skip if trainer already has a profile for this sport
                if (TrainerProfile::where('user_id', $trainer->id)->where('sport_id', $sport->id)->exists()) {
                    continue;
                }

                $tier = $tiers->random();
                
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

        // Create a top-rated expert trainer
        $expertTrainer = User::factory()->create([
            'name' => 'Muhammad Ahmed',
            'email' => 'ahmed.expert@sportsclub.pk',
            'is_trainer' => true,
            'user_role' => 'member'
        ]);

        TrainerProfile::factory()->expert()->verified()->available()->highEarner()->create([
            'user_id' => $expertTrainer->id,
            'sport_id' => $sports->first()->id,
            'tier_id' => $tiers->where('tier_name', 'premium')->first()?->id ?? $tiers->first()->id,
            'bio' => 'Expert trainer with 15+ years of experience. Specialized in advanced training techniques and has trained national level athletes.',
            'rating' => 4.9,
            'experience_years' => 15,
            'hourly_rate' => 120.00,
            'total_sessions' => 450,
            'total_earnings' => 54000.00,
            'monthly_earnings' => 8500.00,
        ]);

        // Create a female trainer for female clients
        $femaleTrainer = User::factory()->create([
            'name' => 'Fatima Khan',
            'email' => 'fatima.trainer@sportsclub.pk',
            'gender' => 'female',
            'is_trainer' => true,
            'user_role' => 'member'
        ]);

        TrainerProfile::factory()->intermediate()->verified()->available()->femaleClients()->create([
            'user_id' => $femaleTrainer->id,
            'sport_id' => $sports->random()->id,
            'tier_id' => $tiers->random()->id,
            'bio' => 'Certified female trainer specializing in women\'s fitness and health. Creating a comfortable and supportive environment for female clients.',
            'gender_preference' => 'female',
            'rating' => 4.6,
            'experience_years' => 4,
            'hourly_rate' => 65.00,
        ]);

        // Create a beginner trainer
        $beginnerTrainer = User::factory()->create([
            'name' => 'Ali Hassan',
            'email' => 'ali.beginner@sportsclub.pk',
            'is_trainer' => true,
            'user_role' => 'member'
        ]);

        TrainerProfile::factory()->beginner()->verified()->available()->create([
            'user_id' => $beginnerTrainer->id,
            'sport_id' => $sports->random()->id,
            'tier_id' => $tiers->where('tier_name', 'basic')->first()?->id ?? $tiers->first()->id,
            'bio' => 'Enthusiastic new trainer ready to help you achieve your fitness goals. Fresh perspective with latest training methodologies.',
            'rating' => 4.1,
            'experience_years' => 1,
            'hourly_rate' => 35.00,
        ]);

        // Create an unverified trainer
        $unverifiedTrainer = User::factory()->create([
            'name' => 'Omar Malik',
            'email' => 'omar.unverified@sportsclub.pk',
            'is_trainer' => true,
            'user_role' => 'member'
        ]);

        TrainerProfile::factory()->unverified()->available()->create([
            'user_id' => $unverifiedTrainer->id,
            'sport_id' => $sports->random()->id,
            'tier_id' => $tiers->random()->id,
            'bio' => 'Passionate trainer awaiting verification. Eager to start helping clients achieve their fitness goals.',
            'rating' => 0.0,
            'experience_years' => 2,
            'hourly_rate' => 40.00,
            'total_sessions' => 0,
            'total_earnings' => 0.00,
            'monthly_earnings' => 0.00,
        ]);

        $this->command->info('Specific trainer profiles created for testing!');
    }
}
