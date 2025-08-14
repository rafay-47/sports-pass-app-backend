<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class SportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sports = [
            ['name' => 'Football', 'icon' => '⚽', 'color' => '#2E8B57'],
            ['name' => 'Cricket', 'icon' => '🏏', 'color' => '#FF4500'],
            ['name' => 'Basketball', 'icon' => '🏀', 'color' => '#FF6347'],
            ['name' => 'Tennis', 'icon' => '🎾', 'color' => '#32CD32'],
            ['name' => 'Badminton', 'icon' => '🏸', 'color' => '#4169E1'],
            ['name' => 'Swimming', 'icon' => '🏊', 'color' => '#20B2AA'],
            ['name' => 'Volleyball', 'icon' => '🏐', 'color' => '#FFD700'],
            ['name' => 'Table Tennis', 'icon' => '🏓', 'color' => '#DC143C'],
        ];

    $sport = fake()->randomElement($sports);
    $name = $sport['name'];

        return [
            'name' => $name,
            'display_name' => $name,
            'icon' => $sport['icon'],
            'color' => $sport['color'],
            'description' => fake()->paragraph(2),
            'is_active' => fake()->boolean(90), // 90% chance of being active
        ];
    }
}
