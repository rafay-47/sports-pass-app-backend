<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed sports
        $this->call(SportSeeder::class);
        
        // Seed tiers
        $this->call(TierSeeder::class);
        
        // Seed test users with different roles
        $this->call(UserSeeder::class);
        
        // Seed memberships (must be last as it depends on users, sports, and tiers)
        $this->call(MembershipSeeder::class);
    }
}
