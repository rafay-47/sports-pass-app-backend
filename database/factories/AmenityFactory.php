<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Amenity;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Amenity>
 */
class AmenityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Seeded names to avoid collisions
        $reserved = [
            'WiFi', 'Parking', 'Locker Rooms', 'Showers', 'Sauna'
        ];

        // Generate a human-friendly name (two words) that does not conflict with reserved names
        do {
            $name = ucfirst($this->faker->unique()->words(2, true));
        } while (in_array($name, $reserved) || Amenity::where('name', $name)->exists());

        return [
            'name' => $name,
            'description' => $this->faker->optional()->sentence(6),
            'is_active' => $this->faker->boolean(90),
            'created_by' => null,
        ];
    }
}
