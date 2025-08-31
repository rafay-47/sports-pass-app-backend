<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\User;
use App\Models\Membership;
use App\Models\Event;
use App\Models\TrainerSession;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some existing users, memberships, events, and trainer sessions
        $users = User::all();
        $memberships = Membership::all();
        $events = Event::all();
        $trainerSessions = TrainerSession::all();

        $payments = [
            // Membership payments
            [
                'user_id' => $users->first()?->id ?? null,
                'transaction_id' => 'TXN_MEM_001',
                'amount' => 2000.00,
                'currency' => 'PKR',
                'payment_method' => 'easypaisa',
                'payment_type' => 'membership',
                'reference_id' => $memberships->first()?->id ?? null,
                'status' => 'completed',
                'payment_gateway_response' => ['transaction_id' => 'EP123456', 'status' => 'success'],
                'payment_date' => now()->subDays(30),
            ],
            [
                'user_id' => $users->skip(1)->first()?->id ?? null,
                'transaction_id' => 'TXN_MEM_002',
                'amount' => 4000.00,
                'currency' => 'PKR',
                'payment_method' => 'jazzcash',
                'payment_type' => 'membership',
                'reference_id' => $memberships->skip(1)->first()?->id ?? null,
                'status' => 'completed',
                'payment_gateway_response' => ['transaction_id' => 'JC789012', 'status' => 'success'],
                'payment_date' => now()->subDays(15),
            ],
            [
                'user_id' => $users->skip(2)->first()?->id ?? null,
                'transaction_id' => 'TXN_MEM_003',
                'amount' => 6000.00,
                'currency' => 'PKR',
                'payment_method' => 'sadapay',
                'payment_type' => 'membership',
                'reference_id' => $memberships->skip(2)->first()?->id ?? null,
                'status' => 'pending',
                'payment_gateway_response' => null,
                'payment_date' => null,
            ],

            // Event registration payments
            [
                'user_id' => $users->first()?->id ?? null,
                'transaction_id' => 'TXN_EVT_001',
                'amount' => 500.00,
                'currency' => 'PKR',
                'payment_method' => 'easypaisa',
                'payment_type' => 'event',
                'reference_id' => $events->first()?->id ?? null,
                'status' => 'completed',
                'payment_gateway_response' => ['transaction_id' => 'EP_EVT_001', 'status' => 'success'],
                'payment_date' => now()->subDays(7),
            ],
            [
                'user_id' => $users->skip(1)->first()?->id ?? null,
                'transaction_id' => 'TXN_EVT_002',
                'amount' => 750.00,
                'currency' => 'PKR',
                'payment_method' => 'bank',
                'payment_type' => 'event',
                'reference_id' => $events->skip(1)->first()?->id ?? null,
                'status' => 'completed',
                'payment_gateway_response' => ['transaction_id' => 'BANK_EVT_002', 'status' => 'success'],
                'payment_date' => now()->subDays(3),
            ],

            // Trainer session payments
            [
                'user_id' => $users->first()?->id ?? null,
                'transaction_id' => 'TXN_TRN_001',
                'amount' => 2500.00,
                'currency' => 'PKR',
                'payment_method' => 'jazzcash',
                'payment_type' => 'trainer_session',
                'reference_id' => $trainerSessions->first()?->id ?? null,
                'status' => 'completed',
                'payment_gateway_response' => ['transaction_id' => 'JC_TRN_001', 'status' => 'success'],
                'payment_date' => now()->subDays(5),
            ],
            [
                'user_id' => $users->skip(1)->first()?->id ?? null,
                'transaction_id' => 'TXN_TRN_002',
                'amount' => 3000.00,
                'currency' => 'PKR',
                'payment_method' => 'mastercard',
                'payment_type' => 'trainer_session',
                'reference_id' => $trainerSessions->skip(1)->first()?->id ?? null,
                'status' => 'failed',
                'payment_gateway_response' => ['error' => 'Card declined', 'status' => 'failed'],
                'failure_reason' => 'Insufficient funds',
                'payment_date' => now()->subDays(2),
            ],

            // Service purchase payments
            [
                'user_id' => $users->skip(2)->first()?->id ?? null,
                'transaction_id' => 'TXN_SVC_001',
                'amount' => 1500.00,
                'currency' => 'PKR',
                'payment_method' => 'easypaisa',
                'payment_type' => 'service',
                'reference_id' => null, // Would reference sport_service_id
                'status' => 'completed',
                'payment_gateway_response' => ['transaction_id' => 'EP_SVC_001', 'status' => 'success'],
                'payment_date' => now()->subDays(10),
            ],

            // Failed payment example
            [
                'user_id' => $users->first()?->id ?? null,
                'transaction_id' => 'TXN_FLD_001',
                'amount' => 1000.00,
                'currency' => 'PKR',
                'payment_method' => 'sadapay',
                'payment_type' => 'membership',
                'reference_id' => null,
                'status' => 'failed',
                'payment_gateway_response' => ['error' => 'Network timeout', 'status' => 'failed'],
                'failure_reason' => 'Payment gateway timeout',
                'payment_date' => now()->subDays(1),
            ],

            // Refunded payment example
            [
                'user_id' => $users->skip(1)->first()?->id ?? null,
                'transaction_id' => 'TXN_REF_001',
                'amount' => 2000.00,
                'currency' => 'PKR',
                'payment_method' => 'bank',
                'payment_type' => 'event',
                'reference_id' => null,
                'status' => 'refunded',
                'payment_gateway_response' => ['transaction_id' => 'BANK_REF_001', 'status' => 'refunded'],
                'refund_amount' => 2000.00,
                'refund_date' => now()->subDays(1),
                'payment_date' => now()->subDays(5),
            ],
        ];

        foreach ($payments as $paymentData) {
            // Skip if user_id is null (no users exist yet)
            if (!$paymentData['user_id']) {
                continue;
            }

            Payment::updateOrCreate(
                ['transaction_id' => $paymentData['transaction_id']],
                $paymentData
            );
        }

        $this->command->info('Sample payments created successfully!');
        $this->command->info('Created payments for: memberships, events, trainer sessions, and services');
        $this->command->info('Includes examples of completed, pending, failed, and refunded payments');
    }
}
