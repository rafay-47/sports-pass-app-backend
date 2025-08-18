<?php

namespace Database\Factories;

use App\Models\Membership;
use App\Models\User;
use App\Models\Sport;
use App\Models\Tier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Membership::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchaseDate = $this->faker->dateTimeBetween('-6 months', 'now');
        $startDate = Carbon::parse($purchaseDate)->addDays($this->faker->numberBetween(0, 7));
        $duration = $this->faker->randomElement([30, 90, 180, 365]);
        $expiryDate = $startDate->copy()->addDays($duration);

        return [
            'membership_number' => 'MEM' . strtoupper($this->faker->unique()->lexify('??????')),
            'user_id' => User::factory(),
            'sport_id' => Sport::factory(),
            'tier_id' => Tier::factory(),
            'status' => $this->faker->randomElement(['active', 'paused', 'expired', 'cancelled']),
            'purchase_date' => $purchaseDate,
            'start_date' => $startDate,
            'expiry_date' => $expiryDate,
            'auto_renew' => $this->faker->boolean(70), // 70% chance of auto-renew
            'purchase_amount' => $this->faker->randomFloat(2, 1000, 10000),
            'monthly_check_ins' => $this->faker->numberBetween(0, 30),
            'total_spent' => $this->faker->randomFloat(2, 0, 5000),
            'monthly_spent' => $this->faker->randomFloat(2, 0, 1000),
            'total_earnings' => $this->faker->randomFloat(2, 0, 3000),
            'monthly_earnings' => $this->faker->randomFloat(2, 0, 500),
        ];
    }

    /**
     * Indicate that the membership is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'expiry_date' => now()->addDays($this->faker->numberBetween(30, 365)),
        ]);
    }

    /**
     * Indicate that the membership is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expiry_date' => now()->subDays($this->faker->numberBetween(1, 90)),
        ]);
    }

    /**
     * Indicate that the membership is expiring soon.
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'expiry_date' => now()->addDays($this->faker->numberBetween(1, 30)),
        ]);
    }

    /**
     * Indicate that the membership is paused.
     */
    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paused',
        ]);
    }

    /**
     * Indicate that the membership is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Create a membership for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a membership for a specific sport.
     */
    public function forSport(Sport $sport): static
    {
        return $this->state(fn (array $attributes) => [
            'sport_id' => $sport->id,
        ]);
    }

    /**
     * Create a membership with a specific tier.
     */
    public function withTier(Tier $tier): static
    {
        return $this->state(fn (array $attributes) => [
            'tier_id' => $tier->id,
            'sport_id' => $tier->sport_id,
            'purchase_amount' => $tier->price,
        ]);
    }
}
