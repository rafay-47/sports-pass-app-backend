<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Membership;

class ShowMemberships extends Command
{
    protected $signature = 'membership:show';
    protected $description = 'Display created memberships';

    public function handle()
    {
        $memberships = Membership::with(['user:id,name', 'sport:id,name', 'tier:id,tier_name,price'])
            ->orderBy('created_at', 'desc')
            ->get();

        $this->info('📋 Membership Data Summary:');
        $this->line('');

        // Statistics
        $stats = [
            'Total' => $memberships->count(),
            'Active' => $memberships->where('status', 'active')->count(),
            'Expired' => $memberships->where('status', 'expired')->count(),
            'Paused' => $memberships->where('status', 'paused')->count(),
            'Cancelled' => $memberships->where('status', 'cancelled')->count(),
        ];

        foreach ($stats as $label => $count) {
            $this->line("📊 {$label}: {$count}");
        }

        $this->line('');
        $this->info('📝 Membership Details:');
        $this->line('');

        foreach ($memberships as $membership) {
            $status = $this->getStatusIcon($membership->status);
            $this->line("{$status} {$membership->membership_number} | {$membership->user->name} | {$membership->sport->name} ({$membership->tier->tier_name}) | ₹{$membership->tier->price} | Expires: {$membership->expiry_date->format('Y-m-d')}");
        }

        return 0;
    }

    private function getStatusIcon($status): string
    {
        return match($status) {
            'active' => '✅',
            'expired' => '❌',
            'paused' => '⏸️',
            'cancelled' => '🚫',
            default => '❓'
        };
    }
}
