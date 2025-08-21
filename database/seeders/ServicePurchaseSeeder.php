<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ServicePurchase;
use App\Models\User;
use App\Models\Membership;
use App\Models\SportService;

class ServicePurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have users, memberships, and sport services before creating service purchases
        if (User::count() === 0) {
            $this->call(UserSeeder::class);
        }
        
        if (Membership::count() === 0) {
            $this->call(MembershipSeeder::class);
        }
        
        if (SportService::count() === 0) {
            $this->call(SportServiceSeeder::class);
        }

        $this->command->info('Creating service purchases...');

        // Get active memberships to create service purchases for
        $activeMemberships = Membership::where('status', 'active')
            ->where('expiry_date', '>=', now())
            ->with(['user', 'sport'])
            ->get();

        if ($activeMemberships->isEmpty()) {
            $this->command->warn('No active memberships found. Creating some memberships first...');
            Membership::factory(10)->create();
            $activeMemberships = Membership::where('status', 'active')
                ->where('expiry_date', '>=', now())
                ->with(['user', 'sport'])
                ->get();
        }

        // Create service purchases with different statuses
        foreach ($activeMemberships as $membership) {
            // Each membership gets 2-5 service purchases
            $purchaseCount = rand(2, 5);
            
            for ($i = 0; $i < $purchaseCount; $i++) {
                // 60% completed, 20% upcoming, 10% cancelled, 10% expired
                $statusRand = rand(1, 100);
                
                if ($statusRand <= 60) {
                    ServicePurchase::factory()
                        ->forMembership($membership)
                        ->completed()
                        ->create();
                } elseif ($statusRand <= 80) {
                    ServicePurchase::factory()
                        ->forMembership($membership)
                        ->upcoming()
                        ->create();
                } elseif ($statusRand <= 90) {
                    ServicePurchase::factory()
                        ->forMembership($membership)
                        ->cancelled()
                        ->create();
                } else {
                    ServicePurchase::factory()
                        ->forMembership($membership)
                        ->expired()
                        ->create();
                }
            }
        }

        // Create some additional random service purchases
        ServicePurchase::factory(50)->create();

        // Create some expensive service purchases for premium services
        ServicePurchase::factory(10)->expensive()->completed()->create();

        // Create some cheap service purchases for basic services
        ServicePurchase::factory(15)->cheap()->completed()->create();

        $totalPurchases = ServicePurchase::count();
        $this->command->info("Created {$totalPurchases} service purchases.");

        // Display statistics
        $stats = [
            'completed' => ServicePurchase::where('status', 'completed')->count(),
            'upcoming' => ServicePurchase::where('status', 'upcoming')->count(),
            'cancelled' => ServicePurchase::where('status', 'cancelled')->count(),
            'expired' => ServicePurchase::where('status', 'expired')->count(),
        ];

        $this->command->table(
            ['Status', 'Count'],
            [
                ['Completed', $stats['completed']],
                ['Upcoming', $stats['upcoming']],
                ['Cancelled', $stats['cancelled']],
                ['Expired', $stats['expired']],
                ['Total', $totalPurchases],
            ]
        );

        // Display revenue statistics
        $totalRevenue = ServicePurchase::where('status', 'completed')->sum('amount');
        $avgPurchase = ServicePurchase::where('status', 'completed')->avg('amount');
        
        $this->command->info("Total revenue from completed purchases: PKR " . number_format($totalRevenue, 2));
        $this->command->info("Average purchase amount: PKR " . number_format($avgPurchase, 2));
    }
}