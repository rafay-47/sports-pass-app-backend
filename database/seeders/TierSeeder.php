<?php

namespace Database\Seeders;

use App\Models\Sport;
use App\Models\Tier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all sports
        $sports = Sport::all();

        if ($sports->isEmpty()) {
            $this->command->error('No sports found. Please run SportSeeder first.');
            return;
        }

        $this->command->info('Creating sport tiers...');

        // Define tier templates for different sports
        $sportTiers = [
            'Basketball' => [
                'basic' => [
                    'display_name' => 'Basketball Basic',
                    'description' => 'Basic basketball membership with access to courts and group sessions',
                    'price' => 45.00,
                    'duration_days' => 30,
                    'features' => ['Court access', 'Group training', 'Basic equipment rental']
                ],
                'pro' => [
                    'display_name' => 'Basketball Pro',
                    'description' => 'Professional basketball membership with personal training included',
                    'price' => 85.00,
                    'duration_days' => 60,
                    'features' => ['Court access', 'Personal training', 'Equipment rental', 'Advanced sessions']
                ],
                'elite' => [
                    'display_name' => 'Basketball Elite',
                    'description' => 'Elite basketball membership with premium features and coaching',
                    'price' => 150.00,
                    'duration_days' => 90,
                    'features' => ['Premium court access', 'Personal coach', 'All equipment', 'Competition prep', 'Nutrition guidance']
                ]
            ],
            'Soccer' => [
                'basic' => [
                    'display_name' => 'Soccer Basic',
                    'description' => 'Basic soccer membership with field access and team training',
                    'price' => 40.00,
                    'duration_days' => 30,
                    'features' => ['Field access', 'Team training', 'Basic equipment']
                ],
                'pro' => [
                    'display_name' => 'Soccer Pro',
                    'description' => 'Professional soccer membership with specialized training',
                    'price' => 75.00,
                    'duration_days' => 60,
                    'features' => ['Field access', 'Specialized training', 'Equipment rental', 'Tactical sessions']
                ],
                'elite' => [
                    'display_name' => 'Soccer Elite',
                    'description' => 'Elite soccer membership with professional coaching and facilities',
                    'price' => 140.00,
                    'duration_days' => 90,
                    'features' => ['Premium field access', 'Professional coach', 'All equipment', 'Match analysis', 'Fitness program']
                ]
            ],
            'Tennis' => [
                'basic' => [
                    'display_name' => 'Tennis Basic',
                    'description' => 'Basic tennis membership with court booking and group lessons',
                    'price' => 50.00,
                    'duration_days' => 30,
                    'features' => ['Court booking', 'Group lessons', 'Equipment rental']
                ],
                'pro' => [
                    'display_name' => 'Tennis Pro',
                    'description' => 'Professional tennis membership with private lessons',
                    'price' => 90.00,
                    'duration_days' => 60,
                    'features' => ['Priority court booking', 'Private lessons', 'Equipment included', 'Match play']
                ],
                'elite' => [
                    'display_name' => 'Tennis Elite',
                    'description' => 'Elite tennis membership with professional coaching and premium courts',
                    'price' => 180.00,
                    'duration_days' => 90,
                    'features' => ['Premium courts', 'Professional coach', 'Tournament entry', 'Video analysis', 'Fitness training']
                ]
            ],
            'Swimming' => [
                'basic' => [
                    'display_name' => 'Swimming Basic',
                    'description' => 'Basic swimming membership with pool access and group classes',
                    'price' => 35.00,
                    'duration_days' => 30,
                    'features' => ['Pool access', 'Group classes', 'Lane booking']
                ],
                'pro' => [
                    'display_name' => 'Swimming Pro',
                    'description' => 'Professional swimming membership with personal training',
                    'price' => 65.00,
                    'duration_days' => 60,
                    'features' => ['Pool access', 'Personal training', 'Advanced techniques', 'Competitive training']
                ],
                'elite' => [
                    'display_name' => 'Swimming Elite',
                    'description' => 'Elite swimming membership with professional coaching and performance analysis',
                    'price' => 120.00,
                    'duration_days' => 90,
                    'features' => ['Premium pool access', 'Professional coach', 'Performance analysis', 'Competition prep', 'Recovery sessions']
                ]
            ],
            'Boxing' => [
                'basic' => [
                    'display_name' => 'Boxing Basic',
                    'description' => 'Basic boxing membership with gym access and group classes',
                    'price' => 55.00,
                    'duration_days' => 30,
                    'features' => ['Gym access', 'Group classes', 'Basic equipment']
                ],
                'pro' => [
                    'display_name' => 'Boxing Pro',
                    'description' => 'Professional boxing membership with personal training and sparring',
                    'price' => 95.00,
                    'duration_days' => 60,
                    'features' => ['Gym access', 'Personal training', 'Sparring sessions', 'Advanced techniques']
                ],
                'elite' => [
                    'display_name' => 'Boxing Elite',
                    'description' => 'Elite boxing membership with professional coaching and competition training',
                    'price' => 160.00,
                    'duration_days' => 90,
                    'features' => ['Premium gym access', 'Professional coach', 'Competition training', 'Nutrition plan', 'Recovery sessions']
                ]
            ]
        ];

        foreach ($sports as $sport) {
            $sportName = $sport->name;
            
            if (isset($sportTiers[$sportName])) {
                $this->command->info("Creating tiers for {$sportName}...");
                
                foreach ($sportTiers[$sportName] as $tierName => $tierData) {
                    $iconColors = [
                        'basic' => ['icon' => 'https://placehold.co/64x64/silver/black/png?text=Basic', 'color' => '#C0C0C0'],
                        'pro' => ['icon' => 'https://placehold.co/64x64/gold/black/png?text=Pro', 'color' => '#FFD700'],
                        'elite' => ['icon' => 'https://placehold.co/64x64/purple/white/png?text=Elite', 'color' => '#8B008B'],
                    ];
                    
                    $iconColor = $iconColors[$tierName] ?? ['icon' => 'https://placehold.co/64x64/blue/white/png?text=Tier', 'color' => '#4169E1'];
                    
                    // Make the "pro" tier popular for each sport
                    $isPopular = ($tierName === 'pro');
                    
                    Tier::create([
                        'sport_id' => $sport->id,
                        'tier_name' => $tierName,
                        'display_name' => $tierData['display_name'],
                        'description' => $tierData['description'],
                        'icon' => $iconColor['icon'],
                        'color' => $iconColor['color'],
                        'price' => $tierData['price'],
                        'duration_days' => $tierData['duration_days'],
                        'discount_percentage' => 0.00,
                        'start_date' => null,
                        'end_date' => null,
                        'features' => $tierData['features'],
                        'is_active' => true,
                        'is_popular' => $isPopular,
                    ]);
                }
            } else {
                // Create default tiers for sports not in our predefined list
                $this->command->info("Creating default tiers for {$sportName}...");
                
                $defaultTiers = [
                    [
                        'tier_name' => 'basic',
                        'display_name' => "{$sportName} Basic",
                        'description' => "Basic {$sportName} membership package",
                        'price' => 40.00,
                        'duration_days' => 30,
                        'features' => ['Basic access', 'Group sessions', 'Equipment rental'],
                    ],
                    [
                        'tier_name' => 'pro',
                        'display_name' => "{$sportName} Pro",
                        'description' => "Professional {$sportName} membership package",
                        'price' => 75.00,
                        'duration_days' => 60,
                        'features' => ['Full access', 'Personal training', 'Advanced equipment'],
                    ],
                    [
                        'tier_name' => 'elite',
                        'display_name' => "{$sportName} Elite",
                        'description' => "Elite {$sportName} membership package with premium features",
                        'price' => 130.00,
                        'duration_days' => 90,
                        'features' => ['Premium access', 'Professional coach', 'All features', 'Competition support'],
                    ],
                ];

                foreach ($defaultTiers as $tierData) {
                    $iconColors = [
                        'basic' => ['icon' => 'https://placehold.co/64x64/silver/black/png?text=Basic', 'color' => '#C0C0C0'],
                        'pro' => ['icon' => 'https://placehold.co/64x64/gold/black/png?text=Pro', 'color' => '#FFD700'],
                        'elite' => ['icon' => 'https://placehold.co/64x64/purple/white/png?text=Elite', 'color' => '#8B008B'],
                    ];
                    
                    $iconColor = $iconColors[$tierData['tier_name']] ?? ['icon' => 'https://placehold.co/64x64/blue/white/png?text=Tier', 'color' => '#4169E1'];
                    
                    // Make the "pro" tier popular for each sport
                    $isPopular = ($tierData['tier_name'] === 'pro');
                    
                    Tier::create([
                        'sport_id' => $sport->id,
                        'tier_name' => $tierData['tier_name'],
                        'display_name' => $tierData['display_name'],
                        'description' => $tierData['description'],
                        'icon' => $iconColor['icon'],
                        'color' => $iconColor['color'],
                        'price' => $tierData['price'],
                        'duration_days' => $tierData['duration_days'],
                        'discount_percentage' => 0.00,
                        'start_date' => null,
                        'end_date' => null,
                        'features' => $tierData['features'],
                        'is_active' => true,
                        'is_popular' => $isPopular,
                    ]);
                }
            }
        }

        // Create some special promotional tiers
        $this->command->info('Creating promotional tiers...');
        
        $promotionalTiers = [
            [
                'sport_id' => $sports->where('name', 'Basketball')->first()?->id ?? $sports->first()->id,
                'tier_name' => 'summer_special',
                'display_name' => 'Summer Special',
                'description' => 'Limited time summer promotion for basketball training',
                'price' => 60.00,
                'duration_days' => 45,
                'discount_percentage' => 25.00,
                'start_date' => Carbon::now()->addDays(5),
                'end_date' => Carbon::now()->addDays(60),
                'features' => ['Court access', 'Group training', 'Summer camp', 'Equipment rental'],
                'is_active' => true,
                'is_popular' => false,
            ],
            [
                'sport_id' => $sports->where('name', 'Tennis')->first()?->id ?? $sports->first()->id,
                'tier_name' => 'weekend_warrior',
                'display_name' => 'Weekend Warrior',
                'description' => 'Perfect for weekend tennis enthusiasts',
                'price' => 80.00,
                'duration_days' => 60,
                'discount_percentage' => 15.00,
                'start_date' => null,
                'end_date' => null,
                'features' => ['Weekend court access', 'Saturday coaching', 'Equipment included'],
                'is_active' => true,
                'is_popular' => false,
            ]
        ];

        foreach ($promotionalTiers as $tierData) {
            if ($tierData['sport_id']) {
                Tier::create($tierData);
            }
        }

        $totalTiers = Tier::count();
        $this->command->info("Created {$totalTiers} sport tiers successfully!");
    }
}
