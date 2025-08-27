<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Club;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Club>
 */
class ClubFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['male', 'female', 'mixed'];
        $types = ['Gym', 'Sports Club', 'Fitness Center', 'Recreation Center', 'Sports Academy'];
        $priceRanges = ['$', '$$', '$$$', '$$$$'];

        // Generate realistic timings
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $timings = [];
        foreach ($days as $day) {
            $isOpen = $this->faker->boolean(90); // 90% chance of being open
            $timings[$day] = [
                'isOpen' => $isOpen,
                'open' => $isOpen ? $this->faker->time('H:i', '06:00') : '00:00',
                'close' => $isOpen ? $this->faker->time('H:i', '22:00') : '00:00'
            ];
        }

        // Generate pricing
        $pricing = [
            'basic' => $this->faker->numberBetween(500, 2000),
            'standard' => $this->faker->numberBetween(2000, 5000),
            'premium' => $this->faker->numberBetween(5000, 10000)
        ];

        // Get a random owner (trainer or admin)
        $owner = User::whereIn('user_role', ['admin', 'trainer'])->inRandomOrder()->first();
        if (!$owner) {
            $owner = User::factory()->create(['user_role' => 'admin']);
        }

        return [
            'owner_id' => $owner->id,
            'name' => $this->faker->company() . ' ' . $this->faker->randomElement(['Sports Club', 'Fitness Center', 'Gym', 'Arena']),
            'type' => $this->faker->randomElement($types),
            'description' => $this->faker->paragraph(3),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'latitude' => $this->faker->latitude(-90, 90),
            'longitude' => $this->faker->longitude(-180, 180),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->email(),
            'rating' => $this->faker->randomFloat(1, 0, 5),
            'price_range' => $this->faker->randomElement($priceRanges),
            'category' => $this->faker->randomElement($categories),
            'qr_code' => $this->generateUniqueQrCode(),
            'status' => $this->faker->randomElement(['active', 'pending', 'suspended']),
            'verification_status' => $this->faker->randomElement(['pending', 'verified', 'rejected']),
            'timings' => $timings,
            'pricing' => $pricing,
            'is_active' => $this->faker->boolean(90),
        ];
    }

    /**
     * Generate a unique QR code.
     */
    private function generateUniqueQrCode(): string
    {
        do {
            $qrCode = 'CLUB-' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (Club::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }

    /**
     * Create an active club.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'verification_status' => 'verified',
            'is_active' => true,
        ]);
    }

    /**
     * Create a verified club.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'verified',
        ]);
    }

    /**
     * Create a club with specific category.
     */
    public function category(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }
}
