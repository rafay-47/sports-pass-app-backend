<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin1@example.com',
                'phone' => '+923001111111',
                'password_hash' => Hash::make('password123'),
                'user_role' => 'admin',
                'is_trainer' => false,
                'is_verified' => true,
                'is_active' => true,
                'join_date' => now()->toDateString(),
            ],
            [
                'name' => 'Member User',
                'email' => 'member1@example.com',
                'phone' => '+923002222222',
                'password_hash' => Hash::make('password123'),
                'user_role' => 'member',
                'is_trainer' => false,
                'is_verified' => true,
                'is_active' => true,
                'join_date' => now()->toDateString(),
            ],
            [
                'name' => 'Owner User',
                'email' => 'owner1@example.com',
                'phone' => '+923003333333',
                'password_hash' => Hash::make('password123'),
                'user_role' => 'owner',
                'is_trainer' => false,
                'is_verified' => true,
                'is_active' => true,
                'join_date' => now()->toDateString(),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Test users created successfully:');
        $this->command->info('- admin1@example.com (admin role) - password: password123');
        $this->command->info('- member1@example.com (member role) - password: password123');
        $this->command->info('- owner1@example.com (owner role) - password: password123');
    }
}
