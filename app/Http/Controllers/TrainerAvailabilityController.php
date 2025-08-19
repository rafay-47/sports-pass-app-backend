<?php

namespace App\Http\Controllers;

use App\Models\TrainerAvailability;
use App\Models\TrainerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TrainerAvailabilityController extends Controller
{
    /**
     * Display a listing of availability slots.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TrainerAvailability::with(['trainerProfile.user:id,name,email']);

        // Filter by trainer profile
        if ($request->filled('trainer_profile_id')) {
            $query->where('trainer_profile_id', $request->trainer_profile_id);
        }

        // Filter by day of week
        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        // Filter by availability status
        if ($request->filled('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'day_of_week');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if ($sortBy === 'day_of_week') {
            // Custom sort for days of week
            $query->orderByRaw("
                CASE day_of_week 
                    WHEN 'Monday' THEN 1
                    WHEN 'Tuesday' THEN 2 
                    WHEN 'Wednesday' THEN 3
                    WHEN 'Thursday' THEN 4
                    WHEN 'Friday' THEN 5
                    WHEN 'Saturday' THEN 6
                    WHEN 'Sunday' THEN 7
                END {$sortOrder}
            ")->orderBy('start_time', 'asc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $availabilities = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'availabilities' => $availabilities->items(),
                'pagination' => [
                    'current_page' => $availabilities->currentPage(),
                    'last_page' => $availabilities->lastPage(),
                    'per_page' => $availabilities->perPage(),
                    'total' => $availabilities->total(),
                ]
            ]
        ]);
    }

    /**
     * Store a newly created availability slot.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'trainer_profile_id' => 'required|exists:trainer_profiles,id',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_available' => 'boolean',
        ]);

        // Authorization check
        $trainerProfile = TrainerProfile::findOrFail($request->trainer_profile_id);
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && $trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to add availability for this trainer'
            ], 403);
        }

        // Check for time overlap
        $overlap = TrainerAvailability::where('trainer_profile_id', $request->trainer_profile_id)
                                     ->where('day_of_week', $request->day_of_week)
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

        if ($overlap) {
            return response()->json([
                'status' => 'error',
                'message' => 'Time slot overlaps with existing availability'
            ], 422);
        }

        $availability = TrainerAvailability::create($request->all());
        $availability->load(['trainerProfile.user:id,name,email']);

        return response()->json([
            'status' => 'success',
            'message' => 'Availability slot added successfully',
            'data' => [
                'availability' => $availability
            ]
        ], 201);
    }

    /**
     * Display the specified availability slot.
     */
    public function show(TrainerAvailability $trainerAvailability): JsonResponse
    {
        $trainerAvailability->load(['trainerProfile.user:id,name,email']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'availability' => $trainerAvailability
            ]
        ]);
    }

    /**
     * Update the specified availability slot.
     */
    public function update(Request $request, TrainerAvailability $trainerAvailability): JsonResponse
    {
        $request->validate([
            'day_of_week' => 'sometimes|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'is_available' => 'sometimes|boolean',
        ]);

        // Authorization check
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && $trainerAvailability->trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to update this availability slot'
            ], 403);
        }

        // Check for time overlap if time-related fields are being updated
        if ($request->hasAny(['day_of_week', 'start_time', 'end_time'])) {
            $dayOfWeek = $request->get('day_of_week', $trainerAvailability->day_of_week);
            $startTime = $request->get('start_time', $trainerAvailability->start_time);
            $endTime = $request->get('end_time', $trainerAvailability->end_time);

            $overlap = TrainerAvailability::where('trainer_profile_id', $trainerAvailability->trainer_profile_id)
                                         ->where('day_of_week', $dayOfWeek)
                                         ->where('id', '!=', $trainerAvailability->id)
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

            if ($overlap) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Updated time slot would overlap with existing availability'
                ], 422);
            }
        }

        $trainerAvailability->update($request->only(['day_of_week', 'start_time', 'end_time', 'is_available']));
        $trainerAvailability->load(['trainerProfile.user:id,name,email']);

        return response()->json([
            'status' => 'success',
            'message' => 'Availability slot updated successfully',
            'data' => [
                'availability' => $trainerAvailability
            ]
        ]);
    }

    /**
     * Remove the specified availability slot.
     */
    public function destroy(Request $request, TrainerAvailability $trainerAvailability): JsonResponse
    {
        // Authorization check
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && $trainerAvailability->trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to delete this availability slot'
            ], 403);
        }

        $trainerAvailability->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Availability slot deleted successfully'
        ]);
    }

    /**
     * Get availability for a specific trainer.
     */
    public function getByTrainer(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        $query = $trainerProfile->availability();

        // Filter by day if requested
        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        // Filter by availability status
        if ($request->filled('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        $availability = $query->orderByRaw("
            CASE day_of_week 
                WHEN 'Monday' THEN 1
                WHEN 'Tuesday' THEN 2 
                WHEN 'Wednesday' THEN 3
                WHEN 'Thursday' THEN 4
                WHEN 'Friday' THEN 5
                WHEN 'Saturday' THEN 6
                WHEN 'Sunday' THEN 7
            END ASC
        ")->orderBy('start_time', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'trainer_profile' => $trainerProfile->only(['id', 'user_id']),
                'availability' => $availability
            ]
        ]);
    }

    /**
     * Get weekly schedule for a trainer.
     */
    public function getWeeklySchedule(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        $availability = $trainerProfile->availability()
                                      ->where('is_available', true)
                                      ->orderByRaw("
                                          CASE day_of_week 
                                              WHEN 'Monday' THEN 1
                                              WHEN 'Tuesday' THEN 2 
                                              WHEN 'Wednesday' THEN 3
                                              WHEN 'Thursday' THEN 4
                                              WHEN 'Friday' THEN 5
                                              WHEN 'Saturday' THEN 6
                                              WHEN 'Sunday' THEN 7
                                          END ASC
                                      ")
                                      ->orderBy('start_time', 'asc')
                                      ->get()
                                      ->groupBy('day_of_week');

        $weeklySchedule = [];
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($daysOfWeek as $day) {
            $weeklySchedule[$day] = $availability->get($day, collect())->values();
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'trainer_profile' => $trainerProfile->only(['id', 'user_id']),
                'weekly_schedule' => $weeklySchedule
            ]
        ]);
    }

    /**
     * Bulk update availability status.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'trainer_profile_id' => 'required|exists:trainer_profiles,id',
            'is_available' => 'required|boolean',
            'availability_ids' => 'sometimes|array',
            'availability_ids.*' => 'exists:trainer_availability,id',
            'day_of_week' => 'sometimes|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);

        // Authorization check
        $trainerProfile = TrainerProfile::findOrFail($request->trainer_profile_id);
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && $trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to update availability for this trainer'
            ], 403);
        }

        $query = TrainerAvailability::where('trainer_profile_id', $request->trainer_profile_id);

        if ($request->filled('availability_ids')) {
            $query->whereIn('id', $request->availability_ids);
        }

        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        $updatedCount = $query->update(['is_available' => $request->is_available]);

        return response()->json([
            'status' => 'success',
            'message' => "Updated {$updatedCount} availability slot(s) successfully",
            'data' => [
                'updated_count' => $updatedCount
            ]
        ]);
    }
}
