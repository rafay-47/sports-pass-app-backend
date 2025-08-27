<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Facility;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Facility>
 */
class FacilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reserved = [
            'Cafeteria', 'First Aid', 'Physio', 'Indoor Courts'
        ];

        do {
            $name = ucfirst($this->faker->unique()->words(2, true));
        } while (in_array($name, $reserved) || Facility::where('name', $name)->exists());

        return [
            'name' => $name,
            'description' => $this->faker->optional()->sentence(6),
            'is_active' => $this->faker->boolean(90),
            'created_by' => null,
        ];
    }
}
