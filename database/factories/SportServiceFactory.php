<?php

namespace Database\Factories;

use App\Models\Sport;
use App\Models\SportService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SportService>
 */
class SportServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SportService::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $serviceTypes = [
            'Personal Training',
            'Group Training',
            'Equipment Rental',
            'Court Booking',
            'Coaching Session',
            'Fitness Assessment',
            'Nutritional Consultation',
            'Recovery Session',
            'Beginner Class',
            'Advanced Class',
            'Competition Training',
            'Technique Workshop'
        ];

        return [
            'sport_id' => Sport::factory(),
            'service_name' => $this->faker->randomElement($serviceTypes),
            'description' => $this->faker->paragraph(2),
            'base_price' => $this->faker->randomFloat(2, 10, 200),
            'duration_minutes' => $this->faker->randomElement([30, 45, 60, 90, 120]),
            'discount_percentage' => $this->faker->randomFloat(2, 0, 25),
            'is_active' => $this->faker->boolean(85), // 85% chance of being active
        ];
    }

    /**
     * Indicate that the service is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the service is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the service has no discount.
     */
    public function noDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percentage' => 0.00,
        ]);
    }

    /**
     * Create a service for a specific sport.
     */
    public function forSport(Sport $sport): static
    {
        return $this->state(fn (array $attributes) => [
            'sport_id' => $sport->id,
        ]);
    }
}
