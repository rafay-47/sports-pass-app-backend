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
                    SportService::create([
                        'sport_id' => $sport->id,
                        'service_name' => $serviceName,
                        'description' => $serviceData['description'],
                        'base_price' => $serviceData['base_price'],
                        'duration_minutes' => $serviceData['duration_minutes'],
                        'discount_percentage' => $serviceData['discount_percentage'],
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
                    SportService::create([
                        'sport_id' => $sport->id,
                        'service_name' => $serviceData['service_name'],
                        'description' => $serviceData['description'],
                        'base_price' => $serviceData['base_price'],
                        'duration_minutes' => $serviceData['duration_minutes'],
                        'discount_percentage' => $serviceData['discount_percentage'],
                        'is_active' => true,
                    ]);
                }
            }
        }

        $totalServices = SportService::count();
        $this->command->info("Created {$totalServices} sport services successfully!");
    }
}
