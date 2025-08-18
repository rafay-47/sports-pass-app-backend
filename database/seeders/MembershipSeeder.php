<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Membership;
use App\Models\User;
use App\Models\Sport;
use App\Models\Tier;
use Carbon\Carbon;

class MembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users, sports, and tiers
        $users = User::all();
        $sports = Sport::where('is_active', true)->with('activeTiers')->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        if ($sports->isEmpty()) {
            $this->command->warn('No active sports found. Please run SportSeeder first.');
            return;
        }

        $this->command->info('Creating memberships...');

        $membershipCount = 0;

        // Create memberships for each user
        foreach ($users as $user) {
            // Each user gets 1-3 different sport memberships
            $numberOfMemberships = rand(1, 3);
            $selectedSports = $sports->random($numberOfMemberships);

            foreach ($selectedSports as $sport) {
                $availableTiers = $sport->activeTiers;
                
                if ($availableTiers->isEmpty()) {
                    continue;
                }

                // Select a random tier for this sport
                $tier = $availableTiers->random();

                // Generate membership dates
                $purchaseDate = Carbon::now()->subDays(rand(1, 180));
                $startDate = $purchaseDate->copy()->addDays(rand(0, 7));
                $expiryDate = $startDate->copy()->addDays($tier->duration_days ?? 365);

                // Determine status based on dates and randomization
                $status = $this->determineStatus($startDate, $expiryDate);

                // Calculate realistic usage data
                $monthlyCheckIns = $status === 'active' ? rand(0, 25) : rand(0, 10);
                $totalSpent = $this->calculateSpending($purchaseDate, $status);
                $monthlySpent = $status === 'active' ? rand(0, 2000) : 0;

                // Create membership
                Membership::create([
                    'user_id' => $user->id,
                    'sport_id' => $sport->id,
                    'tier_id' => $tier->id,
                    'status' => $status,
                    'purchase_date' => $purchaseDate,
                    'start_date' => $startDate,
                    'expiry_date' => $expiryDate,
                    'auto_renew' => rand(0, 1) ? true : false,
                    'purchase_amount' => $tier->price,
                    'monthly_check_ins' => $monthlyCheckIns,
                    'total_spent' => $totalSpent,
                    'monthly_spent' => $monthlySpent,
                    'total_earnings' => $user->is_trainer ? rand(0, 10000) : 0,
                    'monthly_earnings' => $user->is_trainer ? rand(0, 2000) : 0,
                ]);

                $membershipCount++;
            }
        }

        // Create some additional memberships with specific scenarios
        $this->createSpecificScenarios($users, $sports);

        $this->command->info("Created {$membershipCount} memberships successfully!");
    }

    /**
     * Determine membership status based on dates and randomization
     */
    private function determineStatus(Carbon $startDate, Carbon $expiryDate): string
    {
        $now = Carbon::now();

        // If membership hasn't started yet
        if ($startDate > $now) {
            return 'active'; // Will be active when it starts
        }

        // If membership has expired
        if ($expiryDate < $now) {
            return rand(0, 1) ? 'expired' : 'cancelled';
        }

        // Active membership - 80% chance of being active
        $random = rand(1, 100);
        if ($random <= 80) {
            return 'active';
        } elseif ($random <= 90) {
            return 'paused';
        } else {
            return 'cancelled';
        }
    }

    /**
     * Calculate realistic spending based on purchase date and status
     */
    private function calculateSpending(Carbon $purchaseDate, string $status): float
    {
        $monthsSinceStart = $purchaseDate->diffInMonths(Carbon::now());
        
        if ($status === 'cancelled' || $monthsSinceStart === 0) {
            return 0;
        }

        // Base spending per month
        $baseMonthlySpending = rand(200, 1500);
        
        // Calculate total spending
        $totalSpending = $baseMonthlySpending * min($monthsSinceStart, 6); // Cap at 6 months
        
        return round($totalSpending, 2);
    }

    /**
     * Create specific membership scenarios for testing
     */
    private function createSpecificScenarios($users, $sports): void
    {
        if ($users->count() < 2 || $sports->count() < 2) {
            return;
        }

        $this->command->info('Creating specific test scenarios...');

        $user1 = $users->first();
        $user2 = $users->skip(1)->first();
        $sport1 = $sports->first();
        $sport2 = $sports->skip(1)->first();

        // Scenario 1: Expiring soon membership
        $tier1 = $sport1->activeTiers->first();
        if ($tier1) {
            Membership::create([
                'user_id' => $user1->id,
                'sport_id' => $sport1->id,
                'tier_id' => $tier1->id,
                'status' => 'active',
                'purchase_date' => Carbon::now()->subDays(300),
                'start_date' => Carbon::now()->subDays(300),
                'expiry_date' => Carbon::now()->addDays(15), // Expiring in 15 days
                'auto_renew' => true,
                'purchase_amount' => $tier1->price,
                'monthly_check_ins' => 20,
                'total_spent' => 5000,
                'monthly_spent' => 800,
                'total_earnings' => 0,
                'monthly_earnings' => 0,
            ]);
        }

        // Scenario 2: Recently expired membership
        $tier2 = $sport2->activeTiers->first();
        if ($tier2) {
            Membership::create([
                'user_id' => $user2->id,
                'sport_id' => $sport2->id,
                'tier_id' => $tier2->id,
                'status' => 'expired',
                'purchase_date' => Carbon::now()->subDays(400),
                'start_date' => Carbon::now()->subDays(400),
                'expiry_date' => Carbon::now()->subDays(5), // Expired 5 days ago
                'auto_renew' => false,
                'purchase_amount' => $tier2->price,
                'monthly_check_ins' => 0,
                'total_spent' => 3200,
                'monthly_spent' => 0,
                'total_earnings' => 0,
                'monthly_earnings' => 0,
            ]);
        }

        // Scenario 3: High-usage active membership
        $tier3 = $sport1->activeTiers->where('tier_name', 'premium')->first() ?? $sport1->activeTiers->first();
        if ($tier3 && $users->count() > 2) {
            $user3 = $users->skip(2)->first();
            Membership::create([
                'user_id' => $user3->id,
                'sport_id' => $sport1->id,
                'tier_id' => $tier3->id,
                'status' => 'active',
                'purchase_date' => Carbon::now()->subDays(90),
                'start_date' => Carbon::now()->subDays(90),
                'expiry_date' => Carbon::now()->addDays(275),
                'auto_renew' => true,
                'purchase_amount' => $tier3->price,
                'monthly_check_ins' => 28, // High usage
                'total_spent' => 8500,
                'monthly_spent' => 1200,
                'total_earnings' => $user3->is_trainer ? 15000 : 0,
                'monthly_earnings' => $user3->is_trainer ? 3000 : 0,
            ]);
        }

        // Scenario 4: Paused membership
        if ($sports->count() > 2) {
            $sport3 = $sports->skip(2)->first();
            $tier4 = $sport3->activeTiers->first();
            if ($tier4) {
                Membership::create([
                    'user_id' => $user1->id,
                    'sport_id' => $sport3->id,
                    'tier_id' => $tier4->id,
                    'status' => 'paused',
                    'purchase_date' => Carbon::now()->subDays(60),
                    'start_date' => Carbon::now()->subDays(60),
                    'expiry_date' => Carbon::now()->addDays(305),
                    'auto_renew' => true,
                    'purchase_amount' => $tier4->price,
                    'monthly_check_ins' => 5, // Low usage since paused
                    'total_spent' => 1200,
                    'monthly_spent' => 0,
                    'total_earnings' => 0,
                    'monthly_earnings' => 0,
                ]);
            }
        }

        $this->command->info('Created specific test scenarios successfully!');
    }
}
