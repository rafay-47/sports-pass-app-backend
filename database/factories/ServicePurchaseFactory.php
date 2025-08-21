<?php

namespace Database\Factories;

use App\Models\ServicePurchase;
use App\Models\User;
use App\Models\Membership;
use App\Models\SportService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServicePurchase>
 */
class ServicePurchaseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServicePurchase::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get random user with active membership
        $membership = Membership::where('status', 'active')
        ->where('expiry_date', '>=', now())
        ->inRandomOrder()
        ->first();

        if (!$membership) {
            // Fallback if no active memberships exist
            $membership = Membership::factory()->create();
        }

        // Get random sport service for this membership's sport
        $sportService = SportService::where('sport_id', $membership->sport_id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if (!$sportService) {
            $sportService = SportService::factory()->create(['sport_id' => $membership->sport_id]);
        }

        $statuses = ['completed', 'cancelled', 'upcoming', 'expired'];
        $status = $this->faker->randomElement($statuses);
        
        // Generate service date based on status
        $serviceDate = null;
        if ($status === 'completed') {
            $serviceDate = $this->faker->dateTimeBetween('-6 months', 'now');
        } elseif ($status === 'upcoming') {
            $serviceDate = $this->faker->dateTimeBetween('now', '+3 months');
        } elseif ($status === 'expired') {
            $serviceDate = $this->faker->dateTimeBetween('-3 months', '-1 day');
        }

        // Calculate amount with potential discount
        $basePrice = $sportService->base_price ?? $this->faker->randomFloat(2, 500, 5000);
        $amount = $sportService->discounted_price ?? $basePrice;

        return [
            'user_id' => $membership->user_id,
            'membership_id' => $membership->id,
            'sport_service_id' => $sportService->id,
            'amount' => $amount,
            'status' => $status,
            'service_date' => $serviceDate?->format('Y-m-d'),
            'service_time' => $this->faker->optional(0.7)->time('H:i'),
            'provider' => $this->faker->optional(0.6)->company(),
            'location' => $this->faker->optional(0.8)->randomElement([
                'Main Gym',
                'Sports Complex A',
                'Training Center B',
                'Fitness Studio',
                'Pool Area',
                'Tennis Court 1',
                'Basketball Court',
                'Outdoor Field',
                'Private Training Room',
                'Group Exercise Hall'
            ]),
            'notes' => $this->faker->optional(0.4)->sentence(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (ServicePurchase $servicePurchase) {
            // Ensure service date is set for upcoming status
            if ($servicePurchase->status === 'upcoming' && !$servicePurchase->service_date) {
                $servicePurchase->service_date = $this->faker->dateTimeBetween('now', '+3 months')->format('Y-m-d');
            }
        });
    }

    /**
     * Create a completed service purchase.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'service_date' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
        ]);
    }

    /**
     * Create an upcoming service purchase.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'upcoming',
            'service_date' => $this->faker->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            'service_time' => $this->faker->time('H:i'),
        ]);
    }

    /**
     * Create a cancelled service purchase.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'service_date' => null,
            'service_time' => null,
        ]);
    }

    /**
     * Create an expired service purchase.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'service_date' => $this->faker->dateTimeBetween('-3 months', '-1 day')->format('Y-m-d'),
        ]);
    }

    /**
     * Create service purchase for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(function (array $attributes) use ($user) {
            $membership = $user->memberships()
                ->where('status', 'active')
                ->where('expiry_date', '>=', now())
                ->inRandomOrder()
                ->first();

            if (!$membership) {
                $membership = Membership::factory()->create(['user_id' => $user->id]);
            }

            $sportService = SportService::where('sport_id', $membership->sport_id)
                ->where('is_active', true)
                ->inRandomOrder()
                ->first();

            if (!$sportService) {
                $sportService = SportService::factory()->create(['sport_id' => $membership->sport_id]);
            }

            return [
                'user_id' => $user->id,
                'membership_id' => $membership->id,
                'sport_service_id' => $sportService->id,
                'amount' => $sportService->discounted_price ?? $sportService->base_price,
            ];
        });
    }

    /**
     * Create service purchase for a specific membership.
     */
    public function forMembership(Membership $membership): static
    {
        return $this->state(function (array $attributes) use ($membership) {
            $sportService = SportService::where('sport_id', $membership->sport_id)
                ->where('is_active', true)
                ->inRandomOrder()
                ->first();

            if (!$sportService) {
                $sportService = SportService::factory()->create(['sport_id' => $membership->sport_id]);
            }

            return [
                'user_id' => $membership->user_id,
                'membership_id' => $membership->id,
                'sport_service_id' => $sportService->id,
                'amount' => $sportService->discounted_price ?? $sportService->base_price,
            ];
        });
    }

    /**
     * Create service purchase for a specific sport service.
     */
    public function forSportService(SportService $sportService): static
    {
        return $this->state(function (array $attributes) use ($sportService) {
            $membership = Membership::where('sport_id', $sportService->sport_id)
                ->where('status', 'active')
                ->where('expiry_date', '>=', now())
                ->inRandomOrder()
                ->first();

            if (!$membership) {
                $membership = Membership::factory()->create(['sport_id' => $sportService->sport_id]);
            }

            return [
                'user_id' => $membership->user_id,
                'membership_id' => $membership->id,
                'sport_service_id' => $sportService->id,
                'amount' => $sportService->discounted_price ?? $sportService->base_price,
            ];
        });
    }

    /**
     * Create service purchase with high amount.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->randomFloat(2, 3000, 10000),
        ]);
    }

    /**
     * Create service purchase with low amount.
     */
    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->randomFloat(2, 100, 1000),
        ]);
    }
}