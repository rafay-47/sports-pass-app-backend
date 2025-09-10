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
    //     // Seed sports
    //     $this->call(SportSeeder::class);

    //     // Seed sport services
    //     $this->call(SportServiceSeeder::class);

    // // Seed tiers
    // $this->call(TierSeeder::class);
        
    // // Seed amenities (master list)
    // $this->call(AmenitySeeder::class);

    // // Seed facilities (master list)
    // $this->call(FacilitySeeder::class);
        
    //     // Seed test users with different roles
    //     $this->call(UserSeeder::class);
        
    //     // Seed clubs (depends on users, sports, amenities, and facilities)
    //     $this->call(ClubSeeder::class);
        
    //     // Seed events (depends on sports)
    //     $this->call(EventSeeder::class);
        
    //     // Seed event registrations (depends on events and users)
    //     $this->call(EventRegistrationSeeder::class);
        
        // Seed club images (depends on clubs)
        $this->call(ClubImageSeeder::class);
        
        // Seed trainer profiles (depends on users, sports, and tiers)
        $this->call(TrainerProfileSeeder::class);
        
        // Seed trainer-related data (depends on trainer profiles)
        $this->call(TrainerCertificationSeeder::class);
        $this->call(TrainerSpecialtySeeder::class);
        $this->call(TrainerAvailabilitySeeder::class);
        $this->call(TrainerLocationSeeder::class);
        $this->call(TrainerSessionSeeder::class);
        
        // Seed memberships (must be last as it depends on users, sports, and tiers)
        //$this->call(MembershipSeeder::class);
        
        // Seed check-ins (depends on memberships and clubs)
        $this->call(CheckInSeeder::class);
        
        // Seed service purchases (depends on users, memberships, and sport services)
        //$this->call(ServicePurchaseSeeder::class);

        // Seed payments (depends on users, memberships, events, trainer sessions, etc.)
        //$this->call(PaymentSeeder::class);

        // Seed notifications (depends on users)
        //$this->call(NotificationSeeder::class);
    }
}
