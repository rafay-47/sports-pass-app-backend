<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users
        $users = User::all();

        $notifications = [
            // Welcome notifications
            [
                'user_id' => $users->first()?->id ?? null,
                'title' => 'Welcome to Sports Club Pakistan!',
                'message' => 'Thank you for joining our sports community. Explore available sports and start your fitness journey today!',
                'type' => 'success',
                'is_read' => false,
                'action_url' => '/sports',
                'metadata' => ['category' => 'welcome', 'priority' => 'high'],
                'expires_at' => null,
            ],
            [
                'user_id' => $users->skip(1)->first()?->id ?? null,
                'title' => 'Welcome to Sports Club Pakistan!',
                'message' => 'Thank you for joining our sports community. Explore available sports and start your fitness journey today!',
                'type' => 'success',
                'is_read' => true,
                'action_url' => '/sports',
                'metadata' => ['category' => 'welcome', 'priority' => 'high'],
                'expires_at' => null,
            ],

            // Membership notifications
            [
                'user_id' => $users->first()?->id ?? null,
                'title' => 'Membership Activated',
                'message' => 'Your Gym membership has been successfully activated. You can now access all gym facilities.',
                'type' => 'membership',
                'is_read' => false,
                'action_url' => '/memberships',
                'metadata' => ['membership_type' => 'gym', 'tier' => 'basic'],
                'expires_at' => null,
            ],
            [
                'user_id' => $users->skip(1)->first()?->id ?? null,
                'title' => 'Membership Expiring Soon',
                'message' => 'Your Cricket membership will expire in 7 days. Renew now to continue enjoying premium facilities.',
                'type' => 'warning',
                'is_read' => false,
                'action_url' => '/memberships/renew',
                'metadata' => ['membership_type' => 'cricket', 'days_remaining' => 7],
                'expires_at' => Carbon::now()->addDays(7),
            ],
            [
                'user_id' => $users->skip(2)->first()?->id ?? null,
                'title' => 'Membership Expired',
                'message' => 'Your Tennis membership has expired. Renew your membership to regain access to facilities.',
                'type' => 'error',
                'is_read' => false,
                'action_url' => '/memberships/renew',
                'metadata' => ['membership_type' => 'tennis', 'status' => 'expired'],
                'expires_at' => null,
            ],

            // Event notifications
            [
                'user_id' => $users->first()?->id ?? null,
                'title' => 'New Event: Cricket Tournament',
                'message' => 'A new cricket tournament is coming up! Register now to participate and win exciting prizes.',
                'type' => 'event',
                'is_read' => false,
                'action_url' => '/events/cricket-tournament',
                'metadata' => ['event_type' => 'tournament', 'sport' => 'cricket', 'registration_deadline' => '2025-09-15'],
                'expires_at' => Carbon::now()->addDays(14),
            ],
            [
                'user_id' => $users->skip(1)->first()?->id ?? null,
                'title' => 'Event Registration Confirmed',
                'message' => 'Your registration for the Badminton Workshop has been confirmed. Don\'t forget to attend!',
                'type' => 'success',
                'is_read' => true,
                'action_url' => '/events/my-registrations',
                'metadata' => ['event_type' => 'workshop', 'sport' => 'badminton', 'event_date' => '2025-09-20'],
                'expires_at' => null,
            ],

            // Trainer notifications
            [
                'user_id' => $users->first()?->id ?? null,
                'title' => 'Trainer Session Reminder',
                'message' => 'Your personal training session with Coach Ahmed is scheduled for tomorrow at 10:00 AM.',
                'type' => 'trainer',
                'is_read' => false,
                'action_url' => '/trainer/sessions',
                'metadata' => ['trainer_name' => 'Coach Ahmed', 'session_time' => '2025-09-01 10:00:00'],
                'expires_at' => Carbon::now()->addDays(1),
            ],
            [
                'user_id' => $users->skip(2)->first()?->id ?? null,
                'title' => 'New Trainer Available',
                'message' => 'Expert tennis coach Sarah Johnson is now available for private sessions. Book your session today!',
                'type' => 'info',
                'is_read' => false,
                'action_url' => '/trainers/sarah-johnson',
                'metadata' => ['trainer_name' => 'Sarah Johnson', 'sport' => 'tennis', 'experience_years' => 8],
                'expires_at' => Carbon::now()->addDays(7),
            ],

            // Check-in notifications
            [
                'user_id' => $users->first()?->id ?? null,
                'title' => 'Check-in Successful',
                'message' => 'You have successfully checked into Downtown Gym. Enjoy your workout!',
                'type' => 'checkin',
                'is_read' => true,
                'action_url' => '/checkins',
                'metadata' => ['club_name' => 'Downtown Gym', 'checkin_time' => '2025-08-31 09:30:00'],
                'expires_at' => null,
            ],
            [
                'user_id' => $users->skip(1)->first()?->id ?? null,
                'title' => 'Monthly Check-in Goal Achieved!',
                'message' => 'Congratulations! You have completed 25 check-ins this month. Keep up the great work!',
                'type' => 'success',
                'is_read' => false,
                'action_url' => '/profile/stats',
                'metadata' => ['checkins_this_month' => 25, 'goal' => 25],
                'expires_at' => null,
            ],

            // Payment notifications
            [
                'user_id' => $users->first()?->id ?? null,
                'title' => 'Payment Successful',
                'message' => 'Your payment of PKR 2,000 for Gym membership has been processed successfully.',
                'type' => 'payment',
                'is_read' => true,
                'action_url' => '/payments',
                'metadata' => ['amount' => 2000, 'currency' => 'PKR', 'payment_type' => 'membership'],
                'expires_at' => null,
            ],
            [
                'user_id' => $users->skip(1)->first()?->id ?? null,
                'title' => 'Payment Failed',
                'message' => 'Your payment for Cricket membership could not be processed. Please update your payment method and try again.',
                'type' => 'error',
                'is_read' => false,
                'action_url' => '/payments/retry',
                'metadata' => ['amount' => 4000, 'currency' => 'PKR', 'failure_reason' => 'Card declined'],
                'expires_at' => Carbon::now()->addDays(3),
            ],
            [
                'user_id' => $users->skip(2)->first()?->id ?? null,
                'title' => 'Refund Processed',
                'message' => 'Your refund of PKR 1,500 for the cancelled tennis workshop has been processed and will reflect in your account within 3-5 business days.',
                'type' => 'info',
                'is_read' => false,
                'action_url' => '/payments',
                'metadata' => ['refund_amount' => 1500, 'currency' => 'PKR', 'original_payment' => 'Workshop registration'],
                'expires_at' => null,
            ],

            // System notifications
            [
                'user_id' => $users->first()?->id ?? null,
                'title' => 'New Feature Available',
                'message' => 'We\'ve added a new feature to track your fitness progress. Check it out in your profile!',
                'type' => 'info',
                'is_read' => false,
                'action_url' => '/profile/progress',
                'metadata' => ['feature' => 'fitness_tracker', 'version' => '2.1.0'],
                'expires_at' => Carbon::now()->addDays(30),
            ],
            [
                'user_id' => $users->skip(1)->first()?->id ?? null,
                'title' => 'Maintenance Notice',
                'message' => 'Scheduled maintenance will be performed on September 5th from 2:00 AM to 4:00 AM. The app may be temporarily unavailable.',
                'type' => 'warning',
                'is_read' => false,
                'action_url' => null,
                'metadata' => ['maintenance_start' => '2025-09-05 02:00:00', 'maintenance_end' => '2025-09-05 04:00:00'],
                'expires_at' => Carbon::now()->addDays(5),
            ],
        ];

        foreach ($notifications as $notificationData) {
            // Skip if user_id is null (no users exist yet)
            if (!$notificationData['user_id']) {
                continue;
            }

            Notification::create($notificationData);
        }

        $this->command->info('Sample notifications created successfully!');
        $this->command->info('Created notifications for: welcome, memberships, events, trainers, check-ins, and payments');
        $this->command->info('Includes examples of read/unread, different types, and expiring notifications');
    }
}
