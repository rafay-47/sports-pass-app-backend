<?php

namespace App\Http\Controllers;

use App\Models\TrainerSpecialty;
use App\Models\TrainerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TrainerSpecialtyController extends Controller
{
    /**
     * Display a listing of specialties.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TrainerSpecialty::with(['trainerProfile.user:id,name,email']);

        // Filter by trainer profile
        if ($request->filled('trainer_profile_id')) {
            $query->where('trainer_profile_id', $request->trainer_profile_id);
        }

        // Search by specialty
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('specialty', 'ILIKE', "%{$search}%");
        }

        // Sort
        $sortBy = $request->get('sort_by', 'specialty');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $specialties = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'specialties' => $specialties->items(),
                'pagination' => [
                    'current_page' => $specialties->currentPage(),
                    'last_page' => $specialties->lastPage(),
                    'per_page' => $specialties->perPage(),
                    'total' => $specialties->total(),
                ]
            ]
        ]);
    }

    /**
     * Store a newly created specialty.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'trainer_profile_id' => 'required|exists:trainer_profiles,id',
            'specialty' => 'required|string|max:100',
        ]);

        // Check for duplicate specialty for the same trainer
        $exists = TrainerSpecialty::where('trainer_profile_id', $request->trainer_profile_id)
                                 ->where('specialty', $request->specialty)
                                 ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'This specialty already exists for this trainer'
            ], 422);
        }

        // Authorization check
        $trainerProfile = TrainerProfile::findOrFail($request->trainer_profile_id);
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && $trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to add specialties for this trainer'
            ], 403);
        }

        $specialty = TrainerSpecialty::create([
            'trainer_profile_id' => $request->trainer_profile_id,
            'specialty' => $request->specialty,
        ]);

        $specialty->load(['trainerProfile.user:id,name,email']);

        return response()->json([
            'status' => 'success',
            'message' => 'Specialty added successfully',
            'data' => [
                'specialty' => $specialty
            ]
        ], 201);
    }

    /**
     * Display the specified specialty.
     */
    public function show(TrainerSpecialty $trainerSpecialty): JsonResponse
    {
        $trainerSpecialty->load(['trainerProfile.user:id,name,email']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'specialty' => $trainerSpecialty
            ]
        ]);
    }

    /**
     * Update the specified specialty.
     */
    public function update(Request $request, TrainerSpecialty $trainerSpecialty): JsonResponse
    {
        $request->validate([
            'specialty' => 'required|string|max:100',
        ]);

        // Check for duplicate specialty for the same trainer
        $exists = TrainerSpecialty::where('trainer_profile_id', $trainerSpecialty->trainer_profile_id)
                                 ->where('specialty', $request->specialty)
                                 ->where('id', '!=', $trainerSpecialty->id)
                                 ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'This specialty already exists for this trainer'
            ], 422);
        }

        // Authorization check
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && $trainerSpecialty->trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to update this specialty'
            ], 403);
        }

        $trainerSpecialty->update(['specialty' => $request->specialty]);
        $trainerSpecialty->load(['trainerProfile.user:id,name,email']);

        return response()->json([
            'status' => 'success',
            'message' => 'Specialty updated successfully',
            'data' => [
                'specialty' => $trainerSpecialty
            ]
        ]);
    }

    /**
     * Remove the specified specialty.
     */
    public function destroy(Request $request, TrainerSpecialty $trainerSpecialty): JsonResponse
    {
        // Authorization check
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && $trainerSpecialty->trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to delete this specialty'
            ], 403);
        }

        $trainerSpecialty->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Specialty deleted successfully'
        ]);
    }

    /**
     * Get specialties for a specific trainer.
     */
    public function getByTrainer(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        $specialties = $trainerProfile->specialties()
                                    ->orderBy('specialty', 'asc')
                                    ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'trainer_profile' => $trainerProfile->only(['id', 'user_id']),
                'specialties' => $specialties
            ]
        ]);
    }

    /**
     * Get popular specialties across all trainers.
     */
    public function getPopular(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $popularSpecialties = TrainerSpecialty::getPopularSpecialties($limit);

        return response()->json([
            'status' => 'success',
            'data' => [
                'popular_specialties' => $popularSpecialties
            ]
        ]);
    }

    /**
     * Bulk add specialties for a trainer.
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $request->validate([
            'trainer_profile_id' => 'required|exists:trainer_profiles,id',
            'specialties' => 'required|array|min:1|max:10',
            'specialties.*' => 'required|string|max:100|distinct',
        ]);

        // Authorization check
        $trainerProfile = TrainerProfile::findOrFail($request->trainer_profile_id);
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && $trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to add specialties for this trainer'
            ], 403);
        }

        $addedSpecialties = [];
        $duplicates = [];

        foreach ($request->specialties as $specialtyName) {
            $exists = TrainerSpecialty::where('trainer_profile_id', $request->trainer_profile_id)
                                     ->where('specialty', $specialtyName)
                                     ->exists();

            if ($exists) {
                $duplicates[] = $specialtyName;
            } else {
                $specialty = TrainerSpecialty::create([
                    'trainer_profile_id' => $request->trainer_profile_id,
                    'specialty' => $specialtyName,
                ]);
                $addedSpecialties[] = $specialty;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Specialties processed successfully',
            'data' => [
                'added_specialties' => $addedSpecialties,
                'duplicates' => $duplicates,
                'added_count' => count($addedSpecialties),
                'duplicate_count' => count($duplicates)
            ]
        ], 201);
    }
}
