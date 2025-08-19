<?php

namespace App\Http\Controllers;

use App\Models\TrainerSession;
use App\Models\TrainerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TrainerSessionController extends Controller
{
    /**
     * Display a listing of sessions.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TrainerSession::with(['trainerProfile.user:id,name,email']);

        // Filter by trainer profile
        if ($request->filled('trainer_profile_id')) {
            $query->where('trainer_profile_id', $request->trainer_profile_id);
        }

        // Filter by client
        if ($request->filled('client_user_id')) {
            $query->where('client_user_id', $request->client_user_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('session_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('session_date', '<=', $request->end_date);
        }

        // Filter by sport
        if ($request->filled('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'session_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $sessions = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'sessions' => $sessions->items(),
                'pagination' => [
                    'current_page' => $sessions->currentPage(),
                    'last_page' => $sessions->lastPage(),
                    'per_page' => $sessions->perPage(),
                    'total' => $sessions->total(),
                ]
            ]
        ]);
    }

    /**
     * Store a newly created session.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'trainer_profile_id' => 'required|exists:trainer_profiles,id',
            'client_user_id' => 'required|exists:users,id',
            'sport_id' => 'required|exists:sports,id',
            'session_date' => 'required|date|after:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'session_fee' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Authorization check
        $trainerProfile = TrainerProfile::findOrFail($request->trainer_profile_id);
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && 
            $trainerProfile->user_id !== $user->id && $request->client_user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to create this session'
            ], 403);
        }

        // Check for session conflicts
        $conflict = TrainerSession::where('trainer_profile_id', $request->trainer_profile_id)
                                 ->where('session_date', $request->session_date)
                                 ->where('status', '!=', 'cancelled')
                                 ->where(function ($query) use ($request) {
                                     $query->where(function ($q) use ($request) {
                                         $q->where('start_time', '<=', $request->start_time)
                                           ->where('end_time', '>', $request->start_time);
                                     })->orWhere(function ($q) use ($request) {
                                         $q->where('start_time', '<', $request->end_time)
                                           ->where('end_time', '>=', $request->end_time);
                                     })->orWhere(function ($q) use ($request) {
                                         $q->where('start_time', '>=', $request->start_time)
                                           ->where('end_time', '<=', $request->end_time);
                                     });
                                 })
                                 ->exists();

        if ($conflict) {
            return response()->json([
                'status' => 'error',
                'message' => 'Session time conflicts with existing booking'
            ], 422);
        }

        $session = TrainerSession::create($request->all());
        $session->load(['trainerProfile.user:id,name,email', 'clientUser:id,name,email', 'sport:id,name']);

        return response()->json([
            'status' => 'success',
            'message' => 'Session created successfully',
            'data' => [
                'session' => $session
            ]
        ], 201);
    }

    /**
     * Display the specified session.
     */
    public function show(TrainerSession $trainerSession): JsonResponse
    {
        $trainerSession->load(['trainerProfile.user:id,name,email', 'clientUser:id,name,email', 'sport:id,name']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'session' => $trainerSession
            ]
        ]);
    }

    /**
     * Update the specified session.
     */
    public function update(Request $request, TrainerSession $trainerSession): JsonResponse
    {
        $request->validate([
            'session_date' => 'sometimes|date',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'session_fee' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled,no_show',
            'notes' => 'nullable|string|max:1000',
            'rating' => 'sometimes|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        // Authorization check
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && 
            $trainerSession->trainerProfile->user_id !== $user->id && 
            $trainerSession->client_user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to update this session'
            ], 403);
        }

        // Restrict certain updates based on user role
        $allowedFields = ['notes'];
        
        if (in_array($user->user_role, ['admin', 'owner']) || 
            $trainerSession->trainerProfile->user_id === $user->id) {
            $allowedFields = array_merge($allowedFields, ['session_date', 'start_time', 'end_time', 'session_fee', 'status']);
        }
        
        if ($trainerSession->client_user_id === $user->id) {
            $allowedFields = array_merge($allowedFields, ['rating', 'feedback']);
        }

        // Check for session conflicts if time/date is being updated
        if ($request->hasAny(['session_date', 'start_time', 'end_time'])) {
            $sessionDate = $request->get('session_date', $trainerSession->session_date);
            $startTime = $request->get('start_time', $trainerSession->start_time);
            $endTime = $request->get('end_time', $trainerSession->end_time);

            $conflict = TrainerSession::where('trainer_profile_id', $trainerSession->trainer_profile_id)
                                     ->where('session_date', $sessionDate)
                                     ->where('status', '!=', 'cancelled')
                                     ->where('id', '!=', $trainerSession->id)
                                     ->where(function ($query) use ($startTime, $endTime) {
                                         $query->where(function ($q) use ($startTime, $endTime) {
                                             $q->where('start_time', '<=', $startTime)
                                               ->where('end_time', '>', $startTime);
                                         })->orWhere(function ($q) use ($startTime, $endTime) {
                                             $q->where('start_time', '<', $endTime)
                                               ->where('end_time', '>=', $endTime);
                                         })->orWhere(function ($q) use ($startTime, $endTime) {
                                             $q->where('start_time', '>=', $startTime)
                                               ->where('end_time', '<=', $endTime);
                                         });
                                     })
                                     ->exists();

            if ($conflict) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Updated session time conflicts with existing booking'
                ], 422);
            }
        }

        $trainerSession->update($request->only($allowedFields));
        $trainerSession->load(['trainerProfile.user:id,name,email', 'clientUser:id,name,email', 'sport:id,name']);

        return response()->json([
            'status' => 'success',
            'message' => 'Session updated successfully',
            'data' => [
                'session' => $trainerSession
            ]
        ]);
    }

    /**
     * Remove the specified session.
     */
    public function destroy(Request $request, TrainerSession $trainerSession): JsonResponse
    {
        // Authorization check
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && 
            $trainerSession->trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to delete this session'
            ], 403);
        }

        $trainerSession->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Session deleted successfully'
        ]);
    }

    /**
     * Get sessions for a specific trainer.
     */
    public function getByTrainer(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        $query = $trainerProfile->sessions()->with(['clientUser:id,name,email', 'sport:id,name']);

        // Filter by status if requested
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('session_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('session_date', '<=', $request->end_date);
        }

        $sessions = $query->orderBy('session_date', 'desc')
                         ->orderBy('start_time', 'asc')
                         ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => [
                'trainer_profile' => $trainerProfile->only(['id', 'user_id']),
                'sessions' => $sessions->items(),
                'pagination' => [
                    'current_page' => $sessions->currentPage(),
                    'last_page' => $sessions->lastPage(),
                    'per_page' => $sessions->perPage(),
                    'total' => $sessions->total(),
                ]
            ]
        ]);
    }

    /**
     * Get sessions for a specific client.
     */
    public function getByClient(Request $request): JsonResponse
    {
        $user = $request->user();
        $clientId = $request->get('client_user_id', $user->id);

        // Authorization check
        if (!in_array($user->user_role, ['admin', 'owner']) && $clientId !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to view these sessions'
            ], 403);
        }

        $query = TrainerSession::with(['trainerProfile.user:id,name,email', 'sport:id,name'])
                              ->where('client_user_id', $clientId);

        // Filter by status if requested
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('session_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('session_date', '<=', $request->end_date);
        }

        $sessions = $query->orderBy('session_date', 'desc')
                         ->orderBy('start_time', 'asc')
                         ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => [
                'client_user_id' => $clientId,
                'sessions' => $sessions->items(),
                'pagination' => [
                    'current_page' => $sessions->currentPage(),
                    'last_page' => $sessions->lastPage(),
                    'per_page' => $sessions->perPage(),
                    'total' => $sessions->total(),
                ]
            ]
        ]);
    }

    /**
     * Cancel a session.
     */
    public function cancel(Request $request, TrainerSession $trainerSession): JsonResponse
    {
        $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        // Authorization check
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && 
            $trainerSession->trainerProfile->user_id !== $user->id && 
            $trainerSession->client_user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to cancel this session'
            ], 403);
        }

        if ($trainerSession->status === 'cancelled') {
            return response()->json([
                'status' => 'error',
                'message' => 'Session is already cancelled'
            ], 422);
        }

        if ($trainerSession->status === 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot cancel a completed session'
            ], 422);
        }

        $trainerSession->update([
            'status' => 'cancelled',
            'notes' => $trainerSession->notes . "\n\nCancellation reason: " . $request->get('cancellation_reason', 'No reason provided')
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Session cancelled successfully',
            'data' => [
                'session' => $trainerSession
            ]
        ]);
    }

    /**
     * Mark session as completed.
     */
    public function complete(Request $request, TrainerSession $trainerSession): JsonResponse
    {
        // Authorization check - Only trainer or admin can mark as completed
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && 
            $trainerSession->trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to complete this session'
            ], 403);
        }

        if ($trainerSession->status === 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Session is already completed'
            ], 422);
        }

        if ($trainerSession->status === 'cancelled') {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot complete a cancelled session'
            ], 422);
        }

        $trainerSession->update(['status' => 'completed']);

        return response()->json([
            'status' => 'success',
            'message' => 'Session marked as completed',
            'data' => [
                'session' => $trainerSession
            ]
        ]);
    }

    /**
     * Get session statistics.
     */
    public function getStats(Request $request): JsonResponse
    {
        $query = TrainerSession::query();

        // Filter by trainer if specified
        if ($request->filled('trainer_profile_id')) {
            $query->where('trainer_profile_id', $request->trainer_profile_id);
        }

        $stats = [
            'total_sessions' => $query->count(),
            'by_status' => TrainerSession::selectRaw('status, COUNT(*) as count')
                                       ->groupBy('status')
                                       ->pluck('count', 'status'),
            'completed_sessions' => $query->where('status', 'completed')->count(),
            'average_rating' => TrainerSession::where('status', 'completed')
                                            ->whereNotNull('rating')
                                            ->avg('rating'),
            'total_revenue' => $query->where('status', 'completed')->sum('session_fee'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'statistics' => $stats
            ]
        ]);
    }
}
