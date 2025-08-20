<?php

namespace Database\Factories;

use App\Models\TrainerSession;
use App\Models\TrainerProfile;
use App\Models\User;
use App\Models\Membership;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TrainerSessionFactory extends Factory
{
    protected $model = TrainerSession::class;

    public function definition(): array
    {
        $sessionTime = $this->faker->time('H:i');
        $durationMinutes = $this->faker->randomElement([60, 90, 120, 180]);
        $startTime = $sessionTime;
        $endTime = date('H:i', strtotime($startTime) + $durationMinutes * 60);
        return [
            'id' => (string) Str::uuid(),
            'trainer_profile_id' => TrainerProfile::factory(),
            'trainee_user_id' => User::factory(),
            'trainee_membership_id' => Membership::factory(),
            'session_date' => $this->faker->date(),
            'session_time' => $sessionTime,
            'duration_minutes' => $durationMinutes,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $this->faker->randomElement(['scheduled', 'completed', 'cancelled', 'no_show']),
            'fee_amount' => $this->faker->randomFloat(2, 100, 1000),
            'payment_status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'location' => $this->faker->address(),
            'notes' => $this->faker->sentence(),
            'trainee_rating' => $this->faker->optional()->numberBetween(1, 5),
            'trainee_feedback' => $this->faker->optional()->sentence(),
            'trainer_notes' => $this->faker->optional()->sentence(),
        ];
    }
}
