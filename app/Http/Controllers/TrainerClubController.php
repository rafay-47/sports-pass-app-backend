<?php

namespace App\Http\Controllers;

use App\Models\TrainerProfile;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TrainerClubController extends Controller
{
    /**
     * Display a listing of trainer's clubs.
     */
    public function index(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        $clubs = $trainerProfile->clubs()
            ->with(['sport', 'amenities', 'facilities', 'primaryImage'])
            ->orderBy('trainer_clubs.is_primary', 'desc')
            ->orderBy('clubs.name')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => [
                'clubs' => $clubs->items(),
                'pagination' => [
                    'current_page' => $clubs->currentPage(),
                    'last_page' => $clubs->lastPage(),
                    'per_page' => $clubs->perPage(),
                    'total' => $clubs->total(),
                ]
            ]
        ]);
    }

    /**
     * Get clubs associated with a specific trainer.
     */
    public function getByTrainer(TrainerProfile $trainerProfile): JsonResponse
    {
        $clubs = $trainerProfile->clubs()
            ->select('clubs.*', 'trainer_clubs.is_primary', 'trainer_clubs.created_at as association_date')
            ->orderBy('trainer_clubs.is_primary', 'desc')
            ->orderBy('clubs.name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'trainer' => [
                    'id' => $trainerProfile->id,
                    'name' => $trainerProfile->user->name,
                    'sport' => $trainerProfile->sport->name,
                ],
                'clubs' => $clubs
            ]
        ]);
    }

    /**
     * Get trainers associated with a specific club.
     */
    public function getByClub(Club $club): JsonResponse
    {
        $trainers = $club->trainers()
            ->with(['user:id,name,email,phone', 'sport:id,name,display_name'])
            ->select('trainer_profiles.*', 'trainer_clubs.is_primary', 'trainer_clubs.created_at as association_date')
            ->orderBy('trainer_clubs.is_primary', 'desc')
            ->orderBy('trainer_profiles.rating', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'club' => [
                    'id' => $club->id,
                    'name' => $club->name,
                    'address' => $club->address,
                    'city' => $club->city,
                ],
                'trainers' => $trainers
            ]
        ]);
    }

    /**
     * Store a new trainer-club association.
     */
    public function store(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        $request->validate([
            'club_id' => 'required|uuid|exists:clubs,id',
            'is_primary' => 'boolean'
        ]);

        // Check if the club is already associated
        if ($trainerProfile->clubs()->where('club_id', $request->club_id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Club is already associated with this trainer'
            ], 400);
        }

        // If setting as primary, remove primary status from other clubs
        if ($request->is_primary) {
            $trainerProfile->clubs()->update(['is_primary' => false]);
        }

        $trainerProfile->clubs()->attach($request->club_id, [
            'is_primary' => $request->is_primary ?? false
        ]);

        $club = Club::find($request->club_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Club added to trainer successfully',
            'data' => [
                'trainer_club' => [
                    'trainer_profile_id' => $trainerProfile->id,
                    'club_id' => $club->id,
                    'club_name' => $club->name,
                    'is_primary' => $request->is_primary ?? false,
                ]
            ]
        ], 201);
    }

    /**
     * Display the specified trainer-club association.
     */
    public function show(TrainerProfile $trainerProfile, Club $club): JsonResponse
    {
        $association = DB::table('trainer_clubs')
            ->where('trainer_profile_id', $trainerProfile->id)
            ->where('club_id', $club->id)
            ->first();

        if (!$association) {
            return response()->json([
                'status' => 'error',
                'message' => 'Trainer is not associated with this club'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'trainer_club' => [
                    'trainer_profile_id' => $trainerProfile->id,
                    'trainer_name' => $trainerProfile->user->name,
                    'club_id' => $club->id,
                    'club_name' => $club->name,
                    'is_primary' => (bool) $association->is_primary,
                    'created_at' => $association->created_at,
                    'updated_at' => $association->updated_at,
                ]
            ]
        ]);
    }

    /**
     * Update the specified trainer-club association.
     */
    public function update(Request $request, TrainerProfile $trainerProfile, Club $club): JsonResponse
    {
        $request->validate([
            'is_primary' => 'boolean'
        ]);

        $association = DB::table('trainer_clubs')
            ->where('trainer_profile_id', $trainerProfile->id)
            ->where('club_id', $club->id)
            ->first();

        if (!$association) {
            return response()->json([
                'status' => 'error',
                'message' => 'Trainer is not associated with this club'
            ], 404);
        }

        // If setting as primary, remove primary status from other clubs
        if ($request->is_primary && !$association->is_primary) {
            $trainerProfile->clubs()->update(['is_primary' => false]);
        }

        DB::table('trainer_clubs')
            ->where('trainer_profile_id', $trainerProfile->id)
            ->where('club_id', $club->id)
            ->update([
                'is_primary' => $request->is_primary ?? $association->is_primary,
                'updated_at' => now()
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Trainer-club association updated successfully',
            'data' => [
                'trainer_club' => [
                    'trainer_profile_id' => $trainerProfile->id,
                    'trainer_name' => $trainerProfile->user->name,
                    'club_id' => $club->id,
                    'club_name' => $club->name,
                    'is_primary' => $request->is_primary ?? $association->is_primary,
                ]
            ]
        ]);
    }

    /**
     * Remove the specified trainer-club association.
     */
    public function destroy(TrainerProfile $trainerProfile, Club $club): JsonResponse
    {
        $association = DB::table('trainer_clubs')
            ->where('trainer_profile_id', $trainerProfile->id)
            ->where('club_id', $club->id)
            ->first();

        if (!$association) {
            return response()->json([
                'status' => 'error',
                'message' => 'Trainer is not associated with this club'
            ], 404);
        }

        DB::table('trainer_clubs')
            ->where('trainer_profile_id', $trainerProfile->id)
            ->where('club_id', $club->id)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Club removed from trainer successfully'
        ]);
    }

    /**
     * Set a club as the primary club for a trainer.
     */
    public function setPrimary(TrainerProfile $trainerProfile, Club $club): JsonResponse
    {
        $association = DB::table('trainer_clubs')
            ->where('trainer_profile_id', $trainerProfile->id)
            ->where('club_id', $club->id)
            ->first();

        if (!$association) {
            return response()->json([
                'status' => 'error',
                'message' => 'Trainer is not associated with this club'
            ], 404);
        }

        // Remove primary status from all clubs
        $trainerProfile->clubs()->update(['is_primary' => false]);

        // Set this club as primary
        DB::table('trainer_clubs')
            ->where('trainer_profile_id', $trainerProfile->id)
            ->where('club_id', $club->id)
            ->update([
                'is_primary' => true,
                'updated_at' => now()
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Primary club updated successfully',
            'data' => [
                'trainer_club' => [
                    'trainer_profile_id' => $trainerProfile->id,
                    'trainer_name' => $trainerProfile->user->name,
                    'club_id' => $club->id,
                    'club_name' => $club->name,
                    'is_primary' => true,
                ]
            ]
        ]);
    }

    /**
     * Bulk update trainer's clubs.
     */
    public function bulkUpdate(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        $request->validate([
            'clubs' => 'required|array',
            'clubs.*.club_id' => 'required|uuid|exists:clubs,id',
            'clubs.*.is_primary' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $updatedClubs = [];
            $primaryClubId = null;

            // Find which club should be primary
            foreach ($request->clubs as $clubData) {
                if (isset($clubData['is_primary']) && $clubData['is_primary']) {
                    $primaryClubId = $clubData['club_id'];
                    break;
                }
            }

            // Reset all is_primary flags first
            $trainerProfile->clubs()->update(['is_primary' => false]);

            // Update each club association
            foreach ($request->clubs as $clubData) {
                $clubId = $clubData['club_id'];
                $isPrimary = ($primaryClubId === $clubId) || (isset($clubData['is_primary']) && $clubData['is_primary']);

                // Check if association exists
                $existing = DB::table('trainer_clubs')
                    ->where('trainer_profile_id', $trainerProfile->id)
                    ->where('club_id', $clubId)
                    ->first();

                if ($existing) {
                    // Update existing association
                    DB::table('trainer_clubs')
                        ->where('trainer_profile_id', $trainerProfile->id)
                        ->where('club_id', $clubId)
                        ->update([
                            'is_primary' => $isPrimary,
                            'updated_at' => now()
                        ]);
                } else {
                    // Create new association
                    DB::table('trainer_clubs')->insert([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'trainer_profile_id' => $trainerProfile->id,
                        'club_id' => $clubId,
                        'is_primary' => $isPrimary,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                $club = \App\Models\Club::find($clubId);
                $updatedClubs[] = [
                    'club_id' => $clubId,
                    'club_name' => $club->name,
                    'is_primary' => $isPrimary,
                ];
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Trainer clubs updated successfully',
                'data' => [
                    'trainer' => [
                        'id' => $trainerProfile->id,
                        'name' => $trainerProfile->user->name,
                    ],
                    'clubs' => $updatedClubs
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update trainer clubs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Replace all clubs for a trainer.
     */
    public function replaceAll(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        $request->validate([
            'clubs' => 'required|array',
            'clubs.*.club_id' => 'required|uuid|exists:clubs,id',
            'clubs.*.is_primary' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            // Remove all existing associations
            $trainerProfile->clubs()->detach();

            $createdClubs = [];
            $primaryClubId = null;

            // Find which club should be primary
            foreach ($request->clubs as $clubData) {
                if (isset($clubData['is_primary']) && $clubData['is_primary']) {
                    $primaryClubId = $clubData['club_id'];
                    break;
                }
            }

            // Create new associations
            foreach ($request->clubs as $clubData) {
                $clubId = $clubData['club_id'];
                $isPrimary = ($primaryClubId === $clubId) || (isset($clubData['is_primary']) && $clubData['is_primary']);

                DB::table('trainer_clubs')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'trainer_profile_id' => $trainerProfile->id,
                    'club_id' => $clubId,
                    'is_primary' => $isPrimary,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $club = \App\Models\Club::find($clubId);
                $createdClubs[] = [
                    'club_id' => $clubId,
                    'club_name' => $club->name,
                    'is_primary' => $isPrimary,
                ];
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'All trainer clubs replaced successfully',
                'data' => [
                    'trainer' => [
                        'id' => $trainerProfile->id,
                        'name' => $trainerProfile->user->name,
                    ],
                    'clubs' => $createdClubs
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to replace trainer clubs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update trainer's primary club.
     */
    public function updatePrimaryClub(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        $request->validate([
            'club_id' => 'required|uuid|exists:clubs,id'
        ]);

        // Check if trainer is associated with this club
        $association = DB::table('trainer_clubs')
            ->where('trainer_profile_id', $trainerProfile->id)
            ->where('club_id', $request->club_id)
            ->first();

        if (!$association) {
            return response()->json([
                'status' => 'error',
                'message' => 'Trainer is not associated with this club'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Remove primary status from all clubs
            $trainerProfile->clubs()->update(['is_primary' => false]);

            // Set new primary club
            DB::table('trainer_clubs')
                ->where('trainer_profile_id', $trainerProfile->id)
                ->where('club_id', $request->club_id)
                ->update([
                    'is_primary' => true,
                    'updated_at' => now()
                ]);

            DB::commit();

            $club = \App\Models\Club::find($request->club_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Primary club updated successfully',
                'data' => [
                    'trainer' => [
                        'id' => $trainerProfile->id,
                        'name' => $trainerProfile->user->name,
                    ],
                    'primary_club' => [
                        'id' => $club->id,
                        'name' => $club->name,
                        'address' => $club->address,
                        'city' => $club->city,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update primary club',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update primary status for multiple clubs.
     */
    public function bulkUpdatePrimary(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        $request->validate([
            'primary_club_ids' => 'array',
            'primary_club_ids.*' => 'uuid|exists:clubs,id'
        ]);

        DB::beginTransaction();
        try {
            // Reset all is_primary flags
            $trainerProfile->clubs()->update(['is_primary' => false]);

            $updatedClubs = [];

            // Set primary status for specified clubs
            if ($request->has('primary_club_ids') && !empty($request->primary_club_ids)) {
                foreach ($request->primary_club_ids as $clubId) {
                    // Check if trainer is associated with this club
                    $exists = DB::table('trainer_clubs')
                        ->where('trainer_profile_id', $trainerProfile->id)
                        ->where('club_id', $clubId)
                        ->exists();

                    if ($exists) {
                        DB::table('trainer_clubs')
                            ->where('trainer_profile_id', $trainerProfile->id)
                            ->where('club_id', $clubId)
                            ->update([
                                'is_primary' => true,
                                'updated_at' => now()
                            ]);

                        $club = \App\Models\Club::find($clubId);
                        $updatedClubs[] = [
                            'club_id' => $clubId,
                            'club_name' => $club->name,
                            'is_primary' => true,
                        ];
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Primary club statuses updated successfully',
                'data' => [
                    'trainer' => [
                        'id' => $trainerProfile->id,
                        'name' => $trainerProfile->user->name,
                    ],
                    'primary_clubs' => $updatedClubs
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update primary club statuses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for trainer-club associations.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_associations' => DB::table('trainer_clubs')->count(),
            'trainers_with_clubs' => DB::table('trainer_clubs')->distinct('trainer_profile_id')->count(),
            'clubs_with_trainers' => DB::table('trainer_clubs')->distinct('club_id')->count(),
            'primary_associations' => DB::table('trainer_clubs')->where('is_primary', true)->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'statistics' => $stats
            ]
        ]);
    }
}