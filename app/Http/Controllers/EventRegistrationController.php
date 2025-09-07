<?php

namespace App\Http\Controllers;

use App\Models\EventRegistration;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreEventRegistrationRequest;
use App\Http\Requests\UpdateEventRegistrationRequest;

class EventRegistrationController extends Controller
{
    /**
     * Display a listing of event registrations.
     */
    public function index(Request $request)
    {
        $query = EventRegistration::with(['event.sport', 'user']);

        // Filter by event
        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('registration_date', [$request->start_date, $request->end_date]);
        }

        $registrations = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'registrations' => $registrations
            ]
        ]);
    }

    /**
     * Store a newly created event registration.
     */
    public function store(StoreEventRegistrationRequest $request)
    {
        $event = Event::find($request->event_id);

        // Check if event is full
        if ($event->isFull()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event is full'
            ], 422);
        }

        // Check if user is already registered
        $existingRegistration = EventRegistration::where('event_id', $request->event_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingRegistration) {
            return response()->json([
                'status' => 'error',
                'message' => 'User already registered for this event'
            ], 422);
        }

        // Check registration deadline
        if ($event->registration_deadline && now()->isAfter($event->registration_deadline)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration deadline has passed'
            ], 422);
        }

        $registration = EventRegistration::create([
            'event_id' => $request->event_id,
            'user_id' => $request->user_id,
            'registration_date' => now(),
            'status' => $request->status ?? 'confirmed',
            'payment_status' => $request->payment_status ?? 'pending',
            'payment_amount' => $request->payment_amount ?? $event->fee,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
        ]);

        // Update current participants count
        $event->increment('current_participants');

        return response()->json([
            'status' => 'success',
            'message' => 'Registration created successfully',
            'data' => $registration->load(['event.sport', 'user'])
        ], 201);
    }

    /**
     * Display the specified event registration.
     */
    public function show(EventRegistration $eventRegistration)
    {
        $eventRegistration->load(['event.sport', 'user']);

        return response()->json([
            'status' => 'success',
            'data' => $eventRegistration
        ]);
    }

    /**
     * Update the specified event registration.
     */
    public function update(UpdateEventRegistrationRequest $request, EventRegistration $eventRegistration)
    {
        $oldStatus = $eventRegistration->status;
        $eventRegistration->update($request->validated());

        // Update participant count if status changed
        if ($oldStatus !== $request->status && in_array($request->status, ['confirmed', 'cancelled'])) {
            $event = $eventRegistration->event;
            if ($request->status === 'confirmed' && $oldStatus !== 'confirmed') {
                $event->increment('current_participants');
            } elseif ($request->status === 'cancelled' && $oldStatus === 'confirmed') {
                $event->decrement('current_participants');
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Registration updated successfully',
            'data' => $eventRegistration->load(['event.sport', 'user'])
        ]);
    }

    /**
     * Remove the specified event registration.
     */
    public function destroy(EventRegistration $eventRegistration)
    {
        $event = $eventRegistration->event;

        // If registration was confirmed, decrement participant count
        if ($eventRegistration->status === 'confirmed') {
            $event->decrement('current_participants');
        }

        $eventRegistration->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Registration deleted successfully'
        ]);
    }

    /**
     * Get registrations for a specific event.
     */
    public function getByEvent(Event $event)
    {
        $registrations = $event->registrations()
            ->with('user')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => [
                'registrations' => $registrations->items(),
                'pagination' => [
                    'current_page' => $registrations->currentPage(),
                    'last_page' => $registrations->lastPage(),
                    'per_page' => $registrations->perPage(),
                    'total' => $registrations->total(),
                ]
            ]
        ]);
    }

    /**
     * Get registrations for a specific user.
     */
    public function getByUser(User $user)
    {
        $registrations = $user->eventRegistrations()
            ->with(['event.sport'])
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => [
                'registrations' => $registrations->items(),
                'pagination' => [
                    'current_page' => $registrations->currentPage(),
                    'last_page' => $registrations->lastPage(),
                    'per_page' => $registrations->perPage(),
                    'total' => $registrations->total(),
                ]
            ]
        ]);
    }

    /**
     * Cancel a registration.
     */
    public function cancel(EventRegistration $eventRegistration)
    {
        if ($eventRegistration->status === 'cancelled') {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration is already cancelled'
            ], 422);
        }

        $eventRegistration->update(['status' => 'cancelled']);

        // Decrement participant count
        $eventRegistration->event->decrement('current_participants');

        return response()->json([
            'status' => 'success',
            'message' => 'Registration cancelled successfully',
            'data' => $eventRegistration->load(['event.sport', 'user'])
        ]);
    }

    /**
     * Confirm a registration.
     */
    public function confirm(EventRegistration $eventRegistration)
    {
        if ($eventRegistration->status === 'confirmed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration is already confirmed'
            ], 422);
        }

        // Check if event is full
        if ($eventRegistration->event->isFull()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event is full'
            ], 422);
        }

        $eventRegistration->update(['status' => 'confirmed']);

        // Increment participant count
        $eventRegistration->event->increment('current_participants');

        return response()->json([
            'status' => 'success',
            'message' => 'Registration confirmed successfully',
            'data' => $eventRegistration->load(['event.sport', 'user'])
        ]);
    }

    /**
     * Process payment for registration.
     */
    public function processPayment(Request $request, EventRegistration $eventRegistration)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|max:50',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $eventRegistration->update([
            'payment_status' => 'paid',
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'payment_date' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment processed successfully',
            'data' => $eventRegistration->load(['event.sport', 'user'])
        ]);
    }

    /**
     * Get registration statistics.
     */
    public function statistics()
    {
        $stats = [
            'total_registrations' => EventRegistration::count(),
            'confirmed_registrations' => EventRegistration::where('status', 'confirmed')->count(),
            'pending_registrations' => EventRegistration::where('status', 'pending')->count(),
            'cancelled_registrations' => EventRegistration::where('status', 'cancelled')->count(),
            'paid_registrations' => EventRegistration::where('payment_status', 'paid')->count(),
            'pending_payments' => EventRegistration::where('payment_status', 'pending')->count(),
            'total_revenue' => EventRegistration::where('payment_status', 'paid')->sum('payment_amount'),
            'registrations_by_month' => EventRegistration::selectRaw('DATE_FORMAT(registration_date, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->pluck('count', 'month'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}
