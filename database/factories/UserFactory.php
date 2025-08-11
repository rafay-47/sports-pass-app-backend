<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'password_hash' => Hash::make('password'),
            'name' => fake()->name(),
            'phone' => fake()->unique()->phoneNumber(),
            'date_of_birth' => fake()->optional()->date('Y-m-d'),
            'gender' => fake()->optional()->randomElement(['male', 'female', 'other']),
            'profile_image_url' => fake()->optional()->imageUrl(500, 500, 'people'),
            'is_trainer' => fake()->boolean(20), // 20% chance true
            'is_verified' => fake()->boolean(50),
            'is_active' => true,
            'join_date' => now()->toDateString(),
            'last_login' => fake()->optional()->dateTimeThisYear(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
