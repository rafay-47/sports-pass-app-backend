<?php

namespace Database\Seeders;

use App\Models\Sport;
use App\Models\SportService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SportServiceSeeder extends Seeder
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

        $this->command->info('Creating sport services...');

        // Define services for different sports
        $sportServices = [
            'Basketball' => [
                'Personal Training' => [
                    'description' => 'One-on-one basketball training to improve your skills',
                    'base_price' => 50.00,
                    'duration_minutes' => 60,
                    'discount_percentage' => 0.00,
                ],
                'Group Training' => [
                    'description' => 'Small group basketball training sessions',
                    'base_price' => 30.00,
                    'duration_minutes' => 90,
                    'discount_percentage' => 10.00,
                ],
                'Court Rental' => [
                    'description' => 'Rent a basketball court for practice or games',
                    'base_price' => 80.00,
                    'duration_minutes' => 120,
                    'discount_percentage' => 0.00,
                ],
                'Shooting Clinic' => [
                    'description' => 'Specialized clinic to improve your shooting technique',
                    'base_price' => 35.00,
                    'duration_minutes' => 75,
                    'discount_percentage' => 5.00,
                ],
            ],
            'Soccer' => [
                'Personal Training' => [
                    'description' => 'Individual soccer coaching sessions',
                    'base_price' => 45.00,
                    'duration_minutes' => 60,
                    'discount_percentage' => 0.00,
                ],
                'Team Training' => [
                    'description' => 'Team-based soccer training and tactics',
                    'base_price' => 25.00,
                    'duration_minutes' => 90,
                    'discount_percentage' => 15.00,
                ],
                'Field Rental' => [
                    'description' => 'Rent a soccer field for practice or matches',
                    'base_price' => 100.00,
                    'duration_minutes' => 120,
                    'discount_percentage' => 0.00,
                ],
                'Goalkeeper Training' => [
                    'description' => 'Specialized goalkeeper training sessions',
                    'base_price' => 55.00,
                    'duration_minutes' => 60,
                    'discount_percentage' => 0.00,
                ],
            ],
            'Tennis' => [
                'Private Lessons' => [
                    'description' => 'One-on-one tennis lessons with professional coach',
                    'base_price' => 60.00,
                    'duration_minutes' => 60,
                    'discount_percentage' => 0.00,
                ],
                'Group Lessons' => [
                    'description' => 'Group tennis lessons for beginners to advanced',
                    'base_price' => 25.00,
                    'duration_minutes' => 60,
                    'discount_percentage' => 20.00,
                ],
                'Court Booking' => [
                    'description' => 'Book a tennis court for practice or matches',
                    'base_price' => 30.00,
                    'duration_minutes' => 60,
                    'discount_percentage' => 0.00,
                ],
                'Equipment Rental' => [
                    'description' => 'Rent tennis rackets, balls, and other equipment',
                    'base_price' => 15.00,
                    'duration_minutes' => 120,
                    'discount_percentage' => 0.00,
                ],
            ],
            'Swimming' => [
                'Swimming Lessons' => [
                    'description' => 'Professional swimming lessons for all skill levels',
                    'base_price' => 40.00,
                    'duration_minutes' => 45,
                    'discount_percentage' => 0.00,
                ],
                'Aqua Fitness' => [
                    'description' => 'Water-based fitness classes',
                    'base_price' => 20.00,
                    'duration_minutes' => 45,
                    'discount_percentage' => 10.00,
                ],
                'Lane Rental' => [
                    'description' => 'Rent a swimming lane for training',
                    'base_price' => 25.00,
                    'duration_minutes' => 60,
                    'discount_percentage' => 0.00,
                ],
                'Competitive Training' => [
                    'description' => 'Advanced training for competitive swimmers',
                    'base_price' => 70.00,
                    'duration_minutes' => 90,
                    'discount_percentage' => 5.00,
                ],
            ],
            'Boxing' => [
                'Personal Training' => [
                    'description' => 'One-on-one boxing training sessions',
                    'base_price' => 55.00,
                    'duration_minutes' => 60,
                    'discount_percentage' => 0.00,
                ],
                'Group Classes' => [
                    'description' => 'Group boxing fitness classes',
                    'base_price' => 25.00,
                    'duration_minutes' => 60,
                    'discount_percentage' => 15.00,
                ],
                'Equipment Rental' => [
                    'description' => 'Rent boxing gloves, pads, and equipment',
                    'base_price' => 10.00,
                    'duration_minutes' => 120,
                    'discount_percentage' => 0.00,
                ],
                'Sparring Sessions' => [
                    'description' => 'Supervised sparring sessions',
                    'base_price' => 30.00,
                    'duration_minutes' => 45,
                    'discount_percentage' => 0.00,
                ],
            ],
        ];

        foreach ($sports as $sport) {
            $sportName = $sport->name;
            
            if (isset($sportServices[$sportName])) {
                $this->command->info("Creating services for {$sportName}...");
                
                foreach ($sportServices[$sportName] as $serviceName => $serviceData) {
                    $serviceIcons = [
                        'Personal Training' => 'https://placehold.co/64x64/blue/white/png?text=PT',
                        'Group Training' => 'https://placehold.co/64x64/green/white/png?text=GT',
                        'Equipment Rental' => 'https://placehold.co/64x64/orange/white/png?text=ER',
                        'Court Rental' => 'https://placehold.co/64x64/purple/white/png?text=CR',
                        'Court Booking' => 'https://placehold.co/64x64/purple/white/png?text=CB',
                        'Private Lessons' => 'https://placehold.co/64x64/red/white/png?text=PL',
                        'Group Lessons' => 'https://placehold.co/64x64/yellow/white/png?text=GL',
                        'Swimming Lessons' => 'https://placehold.co/64x64/cyan/white/png?text=SL',
                        'Aqua Fitness' => 'https://placehold.co/64x64/teal/white/png?text=AF',
                        'Lane Rental' => 'https://placehold.co/64x64/blue/white/png?text=LR',
                        'Competitive Training' => 'https://placehold.co/64x64/red/white/png?text=CT',
                        'Shooting Clinic' => 'https://placehold.co/64x64/orange/white/png?text=SC',
                        'Team Training' => 'https://placehold.co/64x64/green/white/png?text=TT',
                        'Field Rental' => 'https://placehold.co/64x64/brown/white/png?text=FR',
                        'Goalkeeper Training' => 'https://placehold.co/64x64/black/white/png?text=GT',
                        'Group Classes' => 'https://placehold.co/64x64/pink/white/png?text=GC',
                        'Sparring Sessions' => 'https://placehold.co/64x64/red/white/png?text=SS',
                        'Strength Training' => 'https://placehold.co/64x64/gray/white/png?text=ST',
                    ];
                    
                    $icon = $serviceIcons[$serviceName] ?? 'https://placehold.co/64x64/blue/white/png?text=SV';
                    
                    // Determine service type and other attributes
                    $type = $this->getServiceType($serviceName);
                    $rating = fake()->randomFloat(2, 3.0, 5.0); // Random rating between 3.0 and 5.0
                    $isPopular = fake()->boolean(30); // 30% chance of being popular
                    
                    SportService::create([
                        'sport_id' => $sport->id,
                        'service_name' => $serviceName,
                        'description' => $serviceData['description'],
                        'icon' => $icon,
                        'base_price' => $serviceData['base_price'],
                        'duration_minutes' => $serviceData['duration_minutes'],
                        'discount_percentage' => $serviceData['discount_percentage'],
                        'rating' => $rating,
                        'type' => $type,
                        'is_popular' => $isPopular,
                        'is_active' => true,
                    ]);
                }
            } else {
                // Create default services for sports not in our predefined list
                $this->command->info("Creating default services for {$sportName}...");
                
                $defaultServices = [
                    [
                        'service_name' => 'Personal Training',
                        'description' => "Personal {$sportName} training session",
                        'base_price' => 50.00,
                        'duration_minutes' => 60,
                        'discount_percentage' => 0.00,
                    ],
                    [
                        'service_name' => 'Group Training',
                        'description' => "Group {$sportName} training session",
                        'base_price' => 30.00,
                        'duration_minutes' => 90,
                        'discount_percentage' => 10.00,
                    ],
                    [
                        'service_name' => 'Equipment Rental',
                        'description' => "Rent {$sportName} equipment",
                        'base_price' => 20.00,
                        'duration_minutes' => 120,
                        'discount_percentage' => 0.00,
                    ],
                ];

                foreach ($defaultServices as $serviceData) {
                    $serviceIcons = [
                        'Personal Training' => 'https://placehold.co/64x64/blue/white/png?text=PT',
                        'Group Training' => 'https://placehold.co/64x64/green/white/png?text=GT',
                        'Equipment Rental' => 'https://placehold.co/64x64/orange/white/png?text=ER',
                    ];
                    
                    $icon = $serviceIcons[$serviceData['service_name']] ?? 'https://placehold.co/64x64/blue/white/png?text=SV';
                    
                    // Determine service type and other attributes
                    $type = $this->getServiceType($serviceData['service_name']);
                    $rating = fake()->randomFloat(2, 3.0, 5.0); // Random rating between 3.0 and 5.0
                    $isPopular = fake()->boolean(30); // 30% chance of being popular
                    
                    SportService::create([
                        'sport_id' => $sport->id,
                        'service_name' => $serviceData['service_name'],
                        'description' => $serviceData['description'],
                        'icon' => $icon,
                        'base_price' => $serviceData['base_price'],
                        'duration_minutes' => $serviceData['duration_minutes'],
                        'discount_percentage' => $serviceData['discount_percentage'],
                        'rating' => $rating,
                        'type' => $type,
                        'is_popular' => $isPopular,
                        'is_active' => true,
                    ]);
                }
            }
        }

        $totalServices = SportService::count();
        $this->command->info("Created {$totalServices} sport services successfully!");
    }

    /**
     * Determine the service type based on service name.
     */
    private function getServiceType(string $serviceName): string
    {
        $trainerServices = [
            'Personal Training', 'Private Lessons', 'Swimming Lessons', 
            'Goalkeeper Training', 'Competitive Training'
        ];
        
        $classServices = [
            'Group Training', 'Group Lessons', 'Group Classes', 'Team Training',
            'Aqua Fitness', 'Shooting Clinic', 'Sparring Sessions'
        ];
        
        $facilityServices = [
            'Court Rental', 'Court Booking', 'Field Rental', 'Lane Rental'
        ];
        
        $equipmentServices = [
            'Equipment Rental'
        ];
        
        if (in_array($serviceName, $trainerServices)) {
            return 'trainer';
        } elseif (in_array($serviceName, $classServices)) {
            return 'class';
        } elseif (in_array($serviceName, $facilityServices)) {
            return 'facility';
        } elseif (in_array($serviceName, $equipmentServices)) {
            return 'equipment';
        } else {
            return 'other';
        }
    }
}
