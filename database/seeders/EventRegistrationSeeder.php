<?php

namespace Database\Seeders;

use App\Models\EventRegistration;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EventRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = Event::all();
        $users = User::where('user_role', 'member')->get();

        if ($events->isEmpty() || $users->isEmpty()) {
            $this->command->info('No events or users found. Skipping event registrations.');
            return;
        }

        $statuses = ['registered', 'confirmed', 'cancelled', 'attended'];
        $paymentStatuses = ['pending', 'paid', 'refunded'];

        foreach ($events as $event) {
            // Register 3-8 random users per event
            $numRegistrations = rand(3, min(8, $users->count()));
            $selectedUsers = $users->random($numRegistrations);

            foreach ($selectedUsers as $user) {
                $registrationDate = Carbon::now()->subDays(rand(0, 30));
                $status = $statuses[array_rand($statuses)];
                $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];

                EventRegistration::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'registration_date' => $registrationDate,
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'payment_amount' => $event->fee ?? rand(10, 100),
                    'notes' => $this->getRandomNote($status),
                ]);
            }
        }

        $this->command->info('Event registrations seeded successfully!');
    }

    /**
     * Get a random note based on status.
     */
    private function getRandomNote(string $status): ?string
    {
        $notes = [
            'registered' => [
                'Looking forward to the event!',
                'Excited to participate',
                'First time attending this type of event',
            ],
            'confirmed' => [
                'Payment completed successfully',
                'Ready for the tournament',
                'All set for the competition',
            ],
            'cancelled' => [
                'Unable to attend due to scheduling conflict',
                'Medical emergency - had to cancel',
                'Family commitment prevents attendance',
            ],
            'attended' => [
                'Great event! Really enjoyed it',
                'Well organized tournament',
                'Had a fantastic time competing',
            ],
        ];

        $statusNotes = $notes[$status] ?? ['Event participation completed'];
        return $statusNotes[array_rand($statusNotes)];
    }
}
