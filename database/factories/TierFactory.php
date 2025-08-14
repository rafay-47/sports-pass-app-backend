<?php

namespace Database\Factories;

use App\Models\Sport;
use App\Models\Tier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tier>
 */
class TierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tierTypes = [
            'basic' => [
                'display_name' => 'Basic Plan',
                'price_range' => [20, 50],
                'duration' => [30, 60],
                'features' => ['Basic access', 'Standard support']
            ],
            'pro' => [
                'display_name' => 'Pro Plan',
                'price_range' => [50, 100],
                'duration' => [60, 90],
                'features' => ['Full access', 'Priority support', 'Advanced features']
            ],
            'elite' => [
                'display_name' => 'Elite Plan',
                'price_range' => [100, 200],
                'duration' => [90, 180],
                'features' => ['Premium access', '24/7 support', 'All features', 'Personal trainer']
            ],
            'beginner' => [
                'display_name' => 'Beginner Package',
                'price_range' => [30, 60],
                'duration' => [30, 45],
                'features' => ['Beginner classes', 'Basic equipment', 'Group sessions']
            ],
            'intermediate' => [
                'display_name' => 'Intermediate Package',
                'price_range' => [60, 120],
                'duration' => [45, 90],
                'features' => ['Intermediate classes', 'Advanced equipment', 'Semi-private sessions']
            ],
            'advanced' => [
                'display_name' => 'Advanced Package',
                'price_range' => [120, 250],
                'duration' => [90, 180],
                'features' => ['Advanced classes', 'Professional equipment', 'Private sessions', 'Competition prep']
            ]
        ];

        $tierName = $this->faker->randomElement(array_keys($tierTypes));
        $tierData = $tierTypes[$tierName];

        $startDate = $this->faker->boolean(30) ? $this->faker->dateTimeBetween('now', '+30 days') : null;
        $endDate = $startDate && $this->faker->boolean(50) ? 
            $this->faker->dateTimeBetween($startDate, '+1 year') : null;

        return [
            'sport_id' => Sport::factory(),
            'tier_name' => $tierName,
            'display_name' => $tierData['display_name'],
            'description' => $this->faker->paragraph(2),
            'icon' => $this->faker->randomElement([
                'https://placehold.co/64x64/silver/black/png?text=Basic',
                'https://placehold.co/64x64/gold/black/png?text=Pro',
                'https://placehold.co/64x64/purple/white/png?text=Elite',
                'https://placehold.co/64x64/green/white/png?text=Tier',
                'https://placehold.co/64x64/blue/white/png?text=Plan',
                'https://placehold.co/64x64/orange/white/png?text=Pack',
            ]),
            'color' => $this->faker->hexColor(),
            'price' => $this->faker->randomFloat(2, $tierData['price_range'][0], $tierData['price_range'][1]),
            'duration_days' => $this->faker->randomElement(range($tierData['duration'][0], $tierData['duration'][1], 15)),
            'discount_percentage' => $this->faker->randomFloat(2, 0, 20),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'features' => $tierData['features'],
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
        ];
    }

    /**
     * Indicate that the tier is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the tier is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the tier has no discount.
     */
    public function noDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percentage' => 0.00,
        ]);
    }

    /**
     * Indicate that the tier is currently available (no date restrictions).
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => null,
            'end_date' => null,
            'is_active' => true,
        ]);
    }

    /**
     * Create a tier for a specific sport.
     */
    public function forSport(Sport $sport): static
    {
        return $this->state(fn (array $attributes) => [
            'sport_id' => $sport->id,
        ]);
    }

    /**
     * Create a tier with specific tier name.
     */
    public function withTierName(string $tierName): static
    {
        return $this->state(fn (array $attributes) => [
            'tier_name' => $tierName,
            'display_name' => ucfirst($tierName) . ' Plan',
        ]);
    }
}
