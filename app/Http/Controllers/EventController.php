<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get user's registered event IDs
        $registeredEventIds = $user->eventRegistrations()
            ->where('status', 'confirmed')
            ->pluck('event_id')
            ->toArray();

        $query = Event::with(['sport', 'club', 'organizer']);

        // Filter by sport
        if ($request->has('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('event_date', [$request->start_date, $request->end_date]);
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'upcoming') {
                $query->upcoming();
            }
        }

        // Search by title or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Exclude events the user has already registered for
        if (!empty($registeredEventIds)) {
            $query->whereNotIn('id', $registeredEventIds);
        }

        $events = $query->get();

        // Get user's registered events
        $registeredEvents = collect([]);
        if (!empty($registeredEventIds)) {
            $registeredEvents = Event::whereIn('id', $registeredEventIds)
                ->with(['sport', 'club', 'organizer'])
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'events' => $events,
                'registeredEvents' => $registeredEvents,
                'meta' => [
                    'total_available' => $events->count(),
                    'total_registered' => $registeredEvents->count(),
                    'user_id' => $user->id
                ]
            ]
        ]);
    }

    /**
     * Store a newly created event.
     */
    public function store(StoreEventRequest $request)
    {
        $event = Event::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Event created successfully',
            'data' => $event->load(['sport', 'club', 'organizer'])
        ], 201);
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        $event->load(['sport', 'club', 'organizer']);

        return response()->json([
            'status' => 'success',
            'data' => $event
        ]);
    }

    /**
     * Update the specified event.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $event->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Event updated successfully',
            'data' => $event->load(['sport', 'club', 'organizer'])
        ]);
    }

    /**
     * Remove the specified event.
     */
    public function destroy(Event $event)
    {
        // Check if there are registrations
        if ($event->registrations()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete event with existing registrations'
            ], 422);
        }

        $event->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Event deleted successfully'
        ]);
    }

    /**
     * Get events by sport.
     */
    public function getBySport(Sport $sport)
    {
        $user = Auth::user();

        // Get user's registered event IDs for this sport
        $registeredEventIds = $user->eventRegistrations()
            ->where('status', 'confirmed')
            ->whereHas('event', function($query) use ($sport) {
                $query->where('sport_id', $sport->id);
            })
            ->pluck('event_id')
            ->toArray();

        $query = $sport->events()
            ->with(['sport', 'club', 'organizer'])
            ->active()
            ->upcoming();

        // Exclude events the user has already registered for
        if (!empty($registeredEventIds)) {
            $query->whereNotIn('events.id', $registeredEventIds);
        }

        $events = $query->paginate(15);

        // Get user's registered events for this sport
        $registeredEvents = collect([]);
        if (!empty($registeredEventIds)) {
            $registeredEvents = Event::whereIn('id', $registeredEventIds)
                ->where('sport_id', $sport->id)
                ->with(['sport', 'club', 'organizer'])
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'events' => $events->items(),
                'registeredEvents' => $registeredEvents,
                'pagination' => [
                    'current_page' => $events->currentPage(),
                    'last_page' => $events->lastPage(),
                    'per_page' => $events->perPage(),
                    'total' => $events->total(),
                ],
                'meta' => [
                    'sport' => [
                        'id' => $sport->id,
                        'name' => $sport->name
                    ],
                    'total_available' => $events->total(),
                    'total_registered' => $registeredEvents->count(),
                    'user_id' => $user->id
                ]
            ]
        ]);
    }

    /**
     * Get events by organizer.
     */
    public function getByOrganizer(Request $request, $userId = null)
    {
        // If no user ID provided, use authenticated user
        if (!$userId) {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Authentication required'
                ], 401);
            }
            $userId = $user->id;
        }

        // Validate user exists
        $organizer = \App\Models\User::find($userId);
        if (!$organizer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organizer not found'
            ], 404);
        }

        $query = Event::where('organizer_id', $userId)
            ->with(['sport', 'club', 'organizer']);

        // Filter by status if provided
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'upcoming') {
                $query->upcoming();
            } elseif ($request->status === 'past') {
                $query->where('event_date', '<', now()->toDateString());
            }
        } else {
            // Default to active and upcoming events
            $query->where('is_active', true)->upcoming();
        }

        // Filter by sport if provided
        if ($request->has('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        $events = $query->orderBy('event_date', 'asc')
            ->orderBy('event_time', 'asc')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => [
                'organizer' => [
                    'id' => $organizer->id,
                    'name' => $organizer->name,
                    'email' => $organizer->email,
                    'user_role' => $organizer->user_role,
                ],
                'events' => $events->items(),
                'pagination' => [
                    'current_page' => $events->currentPage(),
                    'last_page' => $events->lastPage(),
                    'per_page' => $events->perPage(),
                    'total' => $events->total(),
                ]
            ]
        ]);
    }

    /**
     * Get current user's organized events.
     */
    public function getMyEvents(Request $request)
    {
        return $this->getByOrganizer($request);
    }

    /**
     * Register user for an event.
     */
    public function register(Request $request, Event $event)
    {
        $user = Auth::user();

        // Check if event is full
        if ($event->isFull()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event is full'
            ], 422);
        }

        // Check if user is already registered
        $existingRegistration = $event->registrations()->where('user_id', $user->id)->first();
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

        $validator = Validator::make($request->all(), [
            'payment_status' => 'nullable|in:pending,paid',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'registration_date' => now(),
            'status' => 'confirmed',
            'payment_status' => $request->payment_status ?? 'pending',
            'payment_amount' => $event->fee,
            'notes' => $request->notes,
        ]);

        // Update current participants count
        $event->increment('current_participants');

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully registered for event',
            'data' => $registration->load(['event.sport', 'event.club', 'user'])
        ], 201);
    }

    /**
     * Get user's event registrations.
     */
    public function myRegistrations()
    {
        $user = Auth::user();

        $registrations = $user->eventRegistrations()
            ->with(['event.sport', 'event.club', 'event.organizer'])
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
     * Get event statistics.
     */
    public function statistics()
    {
        $stats = [
            'total_events' => Event::count(),
            'active_events' => Event::where('is_active', true)->count(),
            'upcoming_events' => Event::upcoming()->count(),
            'total_registrations' => EventRegistration::count(),
            'events_by_type' => Event::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'events_by_category' => Event::selectRaw('category, COUNT(*) as count')
                ->whereNotNull('category')
                ->groupBy('category')
                ->pluck('count', 'category'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}
