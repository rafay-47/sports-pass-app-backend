<?php

namespace Database\Factories;

use App\Models\TrainerProfile;
use App\Models\User;
use App\Models\Sport;
use App\Models\Tier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrainerProfile>
 */
class TrainerProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TrainerProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $experienceYears = fake()->numberBetween(1, 20);
        $rating = fake()->randomFloat(2, 3.0, 5.0);
        $totalSessions = fake()->numberBetween(0, 500);
        $totalEarnings = $totalSessions * 50 * fake()->randomFloat(1, 0.8, 1.2); // Using fixed rate of 50
        
        return [
            'id' => fake()->uuid(),
            'user_id' => User::factory(),
            'sport_id' => Sport::factory(),
            'tier_id' => Tier::factory(),
            'experience_years' => $experienceYears,
            'bio' => fake()->paragraphs(3, true),
            'rating' => $rating,
            'total_sessions' => $totalSessions,
            'total_earnings' => round($totalEarnings, 2),
            'monthly_earnings' => round($totalEarnings * 0.1, 2), // Roughly 10% of total per month
            'is_verified' => fake()->boolean(70), // 70% chance of being verified
            'is_available' => fake()->boolean(85), // 85% chance of being available
            'gender_preference' => fake()->randomElement(['male', 'female', 'both']),
        ];
    }

    /**
     * Indicate that the trainer is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }

    /**
     * Indicate that the trainer is unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
        ]);
    }

    /**
     * Indicate that the trainer is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => true,
        ]);
    }

    /**
     * Indicate that the trainer is unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }

    /**
     * Indicate that the trainer is a beginner (1-2 years experience).
     */
    public function beginner(): static
    {
        return $this->state(fn (array $attributes) => [
            'experience_years' => fake()->numberBetween(1, 2),
            'rating' => fake()->randomFloat(2, 3.0, 4.2),
        ]);
    }

    /**
     * Indicate that the trainer is intermediate (3-5 years experience).
     */
    public function intermediate(): static
    {
        return $this->state(fn (array $attributes) => [
            'experience_years' => fake()->numberBetween(3, 5),
            'rating' => fake()->randomFloat(2, 3.5, 4.5),
        ]);
    }

    /**
     * Indicate that the trainer is senior (6-10 years experience).
     */
    public function senior(): static
    {
        return $this->state(fn (array $attributes) => [
            'experience_years' => fake()->numberBetween(6, 10),
            'rating' => fake()->randomFloat(2, 4.0, 4.8),
        ]);
    }

    /**
     * Indicate that the trainer is an expert (10+ years experience).
     */
    public function expert(): static
    {
        return $this->state(fn (array $attributes) => [
            'experience_years' => fake()->numberBetween(10, 20),
            'rating' => fake()->randomFloat(2, 4.3, 5.0),
        ]);
    }

    /**
     * Indicate that the trainer has high earnings.
     */
    public function highEarner(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_sessions' => fake()->numberBetween(200, 500),
            'total_earnings' => fake()->randomFloat(2, 10000.00, 50000.00),
            'monthly_earnings' => fake()->randomFloat(2, 2000.00, 8000.00),
        ]);
    }

    /**
     * Indicate that the trainer prefers male clients.
     */
    public function maleClients(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender_preference' => 'male',
        ]);
    }

    /**
     * Indicate that the trainer prefers female clients.
     */
    public function femaleClients(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender_preference' => 'female',
        ]);
    }

    /**
     * Indicate that the trainer accepts both genders.
     */
    public function bothGenders(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender_preference' => 'both',
        ]);
    }
}
