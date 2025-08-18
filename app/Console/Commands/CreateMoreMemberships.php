<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Membership;
use App\Models\User;
use App\Models\Sport;
use App\Models\Tier;
use Carbon\Carbon;

class CreateMoreMemberships extends Command
{
    protected $signature = 'membership:create-more {count=20}';
    protected $description = 'Create additional memberships for testing';

    public function handle()
    {
        $count = (int) $this->argument('count');
        $users = User::all();
        $sports = Sport::with('activeTiers')->get();

        if ($users->isEmpty() || $sports->isEmpty()) {
            $this->error('Need users and sports with tiers to create memberships');
            return 1;
        }

        $this->info("Creating {$count} additional memberships...");
        $bar = $this->output->createProgressBar($count);

        $created = 0;
        for ($i = 0; $i < $count; $i++) {
            $user = $users->random();
            $sport = $sports->random();
            $availableTiers = $sport->activeTiers;

            if ($availableTiers->isEmpty()) {
                continue;
            }

            $tier = $availableTiers->random();

            // Check if user already has membership for this sport
            $existingMembership = Membership::where('user_id', $user->id)
                ->where('sport_id', $sport->id)
                ->where('status', 'active')
                ->where('expiry_date', '>=', now())
                ->exists();

            if ($existingMembership) {
                continue; // Skip if user already has active membership for this sport
            }

            // Generate realistic dates
            $purchaseDate = Carbon::now()->subDays(rand(1, 365));
            $startDate = $purchaseDate->copy()->addDays(rand(0, 7));
            $expiryDate = $startDate->copy()->addDays($tier->duration_days ?? 365);

            // Determine status
            $status = $this->determineStatus($startDate, $expiryDate);

            // Calculate usage data
            $monthlyCheckIns = $status === 'active' ? rand(0, 30) : rand(0, 15);
            $totalSpent = $this->calculateSpending($purchaseDate, $status);
            $monthlySpent = $status === 'active' ? rand(0, 1500) : 0;

            try {
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
                    'total_earnings' => $user->is_trainer ? rand(0, 15000) : 0,
                    'monthly_earnings' => $user->is_trainer ? rand(0, 3000) : 0,
                ]);
                $created++;
            } catch (\Exception $e) {
                // Skip if creation fails (e.g., duplicate membership number)
                continue;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->info("Successfully created {$created} memberships!");

        // Show updated statistics
        $this->call('membership:show');

        return 0;
    }

    private function determineStatus(Carbon $startDate, Carbon $expiryDate): string
    {
        $now = Carbon::now();

        // If membership has expired
        if ($expiryDate < $now) {
            return rand(1, 10) <= 8 ? 'expired' : 'cancelled';
        }

        // If membership hasn't started yet
        if ($startDate > $now) {
            return 'active';
        }

        // Active membership - weighted distribution
        $random = rand(1, 100);
        if ($random <= 75) {
            return 'active';
        } elseif ($random <= 85) {
            return 'paused';
        } else {
            return 'cancelled';
        }
    }

    private function calculateSpending(Carbon $purchaseDate, string $status): float
    {
        if ($status === 'cancelled') {
            return 0;
        }

        $monthsSinceStart = $purchaseDate->diffInMonths(Carbon::now());
        
        if ($monthsSinceStart === 0) {
            return rand(0, 500);
        }

        // Base spending per month varies by status
        $baseMonthlySpending = match($status) {
            'active' => rand(300, 2000),
            'paused' => rand(50, 500),
            'expired' => rand(100, 1200),
            default => rand(0, 800)
        };
        
        // Calculate total spending
        $totalSpending = $baseMonthlySpending * min($monthsSinceStart, 8);
        
        return round($totalSpending, 2);
    }
}
