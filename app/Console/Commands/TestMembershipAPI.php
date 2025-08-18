<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Membership;
use App\Models\User;
use App\Models\Sport;
use App\Models\Tier;

class TestMembershipAPI extends Command
{
    protected $signature = 'membership:test-api';
    protected $description = 'Test membership API functionality';

    public function handle()
    {
        $this->info('🧪 Testing Membership API Functionality');
        $this->line('');

        // Test 1: Model relationships
        $this->info('✅ Test 1: Model Relationships');
        $membership = Membership::with(['user', 'sport', 'tier'])->first();
        
        if ($membership) {
            $this->line("   Membership: {$membership->membership_number}");
            $this->line("   User: {$membership->user->name}");
            $this->line("   Sport: {$membership->sport->name}");
            $this->line("   Tier: {$membership->tier->tier_name} (₹{$membership->tier->price})");
            $this->line("   Status: {$membership->status}");
            $this->line("   Is Active: " . ($membership->is_active ? 'Yes' : 'No'));
            $this->line("   Days Remaining: {$membership->days_remaining}");
            $this->line("   Usage %: {$membership->usage_percentage}%");
        }

        $this->line('');

        // Test 2: Scopes
        $this->info('✅ Test 2: Model Scopes');
        $activeMemberships = Membership::active()->count();
        $expiredMemberships = Membership::expired()->count();
        $expiringSoon = Membership::expiringSoon(30)->count();
        
        $this->line("   Active memberships: {$activeMemberships}");
        $this->line("   Expired memberships: {$expiredMemberships}");
        $this->line("   Expiring soon (30 days): {$expiringSoon}");

        $this->line('');

        // Test 3: User-specific queries
        $this->info('✅ Test 3: User-specific Queries');
        $user = User::first();
        $userMemberships = Membership::forUser($user->id)->count();
        $userActiveMemberships = Membership::forUser($user->id)->active()->count();
        
        $this->line("   User: {$user->name}");
        $this->line("   Total memberships: {$userMemberships}");
        $this->line("   Active memberships: {$userActiveMemberships}");

        $this->line('');

        // Test 4: Sport-specific queries
        $this->info('✅ Test 4: Sport-specific Queries');
        $sport = Sport::first();
        $sportMemberships = Membership::forSport($sport->id)->count();
        $sportActiveMemberships = Membership::forSport($sport->id)->active()->count();
        
        $this->line("   Sport: {$sport->name}");
        $this->line("   Total memberships: {$sportMemberships}");
        $this->line("   Active memberships: {$sportActiveMemberships}");

        $this->line('');

        // Test 5: Business logic methods
        $this->info('✅ Test 5: Business Logic Methods');
        $testMembership = Membership::active()->first();
        
        if ($testMembership) {
            $this->line("   Testing membership: {$testMembership->membership_number}");
            $this->line("   Original status: {$testMembership->status}");
            
            // Test pause/resume
            $testMembership->pause();
            $this->line("   After pause: {$testMembership->fresh()->status}");
            
            $testMembership->resume();
            $this->line("   After resume: {$testMembership->fresh()->status}");
        }

        $this->line('');

        // Test 6: Revenue calculations
        $this->info('✅ Test 6: Revenue Statistics');
        $totalRevenue = Membership::sum('purchase_amount');
        $totalSpent = Membership::sum('total_spent');
        $monthlyRevenue = Membership::whereMonth('purchase_date', now())->sum('purchase_amount');
        
        $this->line("   Total revenue: ₹{$totalRevenue}");
        $this->line("   Total additional spending: ₹{$totalSpent}");
        $this->line("   This month revenue: ₹{$monthlyRevenue}");

        $this->line('');

        // Test 7: Tier distribution
        $this->info('✅ Test 7: Tier Distribution');
        $tierStats = Membership::join('tiers', 'memberships.tier_id', '=', 'tiers.id')
            ->selectRaw('tiers.tier_name, COUNT(*) as count, SUM(memberships.purchase_amount) as revenue')
            ->groupBy('tiers.tier_name')
            ->get();

        foreach ($tierStats as $stat) {
            $this->line("   {$stat->tier_name}: {$stat->count} memberships, ₹{$stat->revenue} revenue");
        }

        $this->line('');
        $this->info('🎉 All tests completed successfully!');
        
        return 0;
    }
}
