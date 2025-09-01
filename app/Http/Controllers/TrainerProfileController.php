<?php

namespace App\Http\Controllers;

use App\Models\TrainerProfile;
use App\Models\User;
use App\Models\Sport;
use App\Models\Tier;
use App\Models\Club;
use App\Models\TrainerCertification;
use App\Http\Requests\StoreTrainerProfileRequest;
use App\Http\Requests\UpdateTrainerProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrainerProfileController extends Controller
{
    /**
     * Display a listing of trainer profiles.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TrainerProfile::with([
            'user:id,name,email,phone,gender',
            'sport:id,name,display_name,icon,color',
            'tier:id,tier_name,display_name,price',
            'specialties' => function ($query) {
                $query->orderBy('specialty', 'asc');
            },
            'certifications' => function ($query) {
                $query->where('is_verified', true)
                      ->orderBy('certification_name', 'asc');
            },
            'locations' => function ($query) use ($request) {
                // Show all locations by default, or filter by is_primary if requested
                if ($request->boolean('primary_locations_only')) {
                    $query->where('is_primary', true);
                }
                $query->orderBy('is_primary', 'desc')
                      ->orderBy('location_type', 'asc');
            },
            'clubs' => function ($query) {
                $query->select('clubs.id', 'clubs.name', 'clubs.address', 'clubs.city')
                      ->orderBy('trainer_clubs.is_primary', 'desc');
            },
            'availability' => function ($query) {
                $query->where('is_available', true)
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
                      ->orderBy('start_time', 'asc');
            }
        ]);

        // Role-based filtering
        $user = $request->user();
        if (!$user || !in_array($user->user_role, ['admin', 'owner'])) {
            // Regular users and unauthenticated users can only see verified and available trainers
            $query->verified()->available();
        }

        // Filter by sport
        if ($request->filled('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        // Filter by verification status
        if ($request->has('verified')) {
            $query->where('is_verified', $request->boolean('verified'));
        }

        // Filter by availability
        if ($request->has('available')) {
            $query->where('is_available', $request->boolean('available'));
        }

        // Filter by active trainers (verified and available)
        if ($request->boolean('active')) {
            $query->active();
        }

        // Filter by rating range
        if ($request->filled('min_rating') || $request->filled('max_rating')) {
            $query->byRating($request->min_rating, $request->max_rating);
        }

        // Filter by experience range
        if ($request->filled('min_experience') || $request->filled('max_experience')) {
            $query->byExperience($request->min_experience, $request->max_experience);
        }

        // Filter by gender preference
        if ($request->filled('gender_preference')) {
            $query->byGenderPreference($request->gender_preference);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'ILIKE', "%{$search}%")
                             ->orWhere('email', 'ILIKE', "%{$search}%");
                })
                ->orWhereHas('sport', function ($sportQuery) use ($search) {
                    $sportQuery->where('name', 'ILIKE', "%{$search}%")
                              ->orWhere('display_name', 'ILIKE', "%{$search}%");
                })
                ->orWhere('bio', 'ILIKE', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'rating');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Handle sorting by related fields
        if ($sortBy === 'user_name') {
            $query->join('users', 'trainer_profiles.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortOrder)
                  ->select('trainer_profiles.*');
        } elseif ($sortBy === 'sport_name') {
            $query->join('sports', 'trainer_profiles.sport_id', '=', 'sports.id')
                  ->orderBy('sports.name', $sortOrder)
                  ->select('trainer_profiles.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $trainers = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'trainers' => $trainers->items(),
                'pagination' => [
                    'current_page' => $trainers->currentPage(),
                    'last_page' => $trainers->lastPage(),
                    'per_page' => $trainers->perPage(),
                    'total' => $trainers->total(),
                ]
            ]
        ]);
    }

    /**
     * Store a newly created trainer profile.
     */
    public function store(StoreTrainerProfileRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $tier = Tier::findOrFail($request->tier_id);
            
            $trainerData = [
                'user_id' => $request->user_id,
                'sport_id' => $request->sport_id,
                'tier_id' => $request->tier_id,
                'experience_years' => $request->experience_years,
                'bio' => $request->bio,
                'rating' => 0.0,
                'total_sessions' => 0,
                'total_earnings' => 0,
                'monthly_earnings' => 0,
                'is_verified' => false, // Always start as unverified
                'is_available' => $request->boolean('is_available', true),
                'gender_preference' => $request->gender_preference ?? 'both',
            ];

            $trainerProfile = TrainerProfile::create($trainerData);
            $trainerProfile->load([
                'user:id,name,email,phone,gender',
                'sport:id,name,display_name,icon,color',
                'tier:id,tier_name,display_name,price',
                'specialties',
                'certifications',
                'locations',
                'clubs' => function ($query) {
                    $query->select('clubs.id', 'clubs.name', 'clubs.address', 'clubs.city')
                          ->orderBy('trainer_clubs.is_primary', 'desc');
                },
                'availability'
            ]);

            // Attach clubs if provided
            if ($request->filled('club_ids')) {
                $clubIds = $request->club_ids;
                $clubsData = [];
                
                foreach ($clubIds as $index => $clubId) {
                    $clubsData[$clubId] = [
                        'is_primary' => $index === 0, // First club is primary
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                $trainerProfile->clubs()->attach($clubsData);
            }

            // Create certificates if provided
            if ($request->filled('certificates')) {
                foreach ($request->certificates as $certificateData) {
                    $trainerProfile->certifications()->create([
                        'certification_name' => $certificateData['certification_name'],
                        'issuing_organization' => $certificateData['issuing_organization'],
                        'issue_date' => $certificateData['issue_date'],
                        'expiry_date' => $certificateData['expiry_date'] ?? null,
                        'certificate_url' => $certificateData['certificate_url'] ?? null,
                        'is_verified' => $certificateData['is_verified'] ?? false,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Trainer profile created successfully',
                'data' => [
                    'trainer_profile' => $trainerProfile
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create trainer profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified trainer profile.
     */
    public function show(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        // Load relationships
        $trainerProfile->load([
            'user:id,name,email,phone,gender,profile_image_url,join_date',
            'sport:id,name,display_name,icon,color',
            'tier:id,tier_name,display_name,price,features',
            'specialties' => function ($query) {
                $query->orderBy('specialty', 'asc');
            },
            'certifications' => function ($query) {
                $query->orderBy('is_verified', 'desc')
                      ->orderBy('certification_name', 'asc');
            },
            'locations' => function ($query) use ($request) {
                // Show all locations by default, or filter by is_primary if requested
                if ($request->boolean('primary_locations_only')) {
                    $query->where('is_primary', true);
                }
                $query->orderBy('is_primary', 'desc')
                      ->orderBy('location_type', 'asc');
            },
            'clubs' => function ($query) {
                $query->select('clubs.id', 'clubs.name', 'clubs.address', 'clubs.city')
                      ->orderBy('trainer_clubs.is_primary', 'desc');
            },
            'availability' => function ($query) {
                $query->where('is_available', true)
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
                      ->orderBy('start_time', 'asc');
            }
        ]);

        // Load recent sessions if requested or for authenticated users viewing their own trainers
        if ($request->boolean('include_sessions') || 
            ($request->user() && $request->user()->user_role !== 'member')) {
            $trainerProfile->load([
                'sessions' => function ($query) {
                    $query->with([
                        'traineeUser:id,name',
                        'traineeMembership:id,membership_number,status'
                    ])
                    ->where('status', '!=', 'cancelled')
                    ->orderBy('session_time', 'desc')
                    ->limit(10);
                }
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'trainer_profile' => $trainerProfile
            ]
        ]);
    }

    /**
     * Update the specified trainer profile.
     */
    public function update(UpdateTrainerProfileRequest $request, TrainerProfile $trainerProfile): JsonResponse
    {
        try {
            // Get validated data
            $validatedData = $request->validated();
            
            // Handle clubs separately
            if (isset($validatedData['club_ids'])) {
                $clubIds = $validatedData['club_ids'];
                $clubsData = [];
                
                foreach ($clubIds as $index => $clubId) {
                    $clubsData[$clubId] = [
                        'is_primary' => $index === 0, // First club is primary
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                $trainerProfile->clubs()->sync($clubsData);
                unset($validatedData['club_ids']);
            }

            // Handle certificates separately
            if (isset($validatedData['certificates'])) {
                // Delete existing certificates and create new ones
                $trainerProfile->certifications()->delete();
                
                foreach ($validatedData['certificates'] as $certificateData) {
                    $trainerProfile->certifications()->create([
                        'certification_name' => $certificateData['certification_name'],
                        'issuing_organization' => $certificateData['issuing_organization'],
                        'issue_date' => $certificateData['issue_date'],
                        'expiry_date' => $certificateData['expiry_date'] ?? null,
                        'certificate_url' => $certificateData['certificate_url'] ?? null,
                        'is_verified' => $certificateData['is_verified'] ?? false,
                    ]);
                }
                
                unset($validatedData['certificates']);
            }

            // Update the trainer profile with remaining data
            $trainerProfile->update($validatedData);
            
            $trainerProfile->load([
                'user:id,name,email,phone,gender',
                'sport:id,name,display_name,icon,color',
                'tier:id,tier_name,display_name,price',
                'specialties',
                'certifications',
                'locations',
                'clubs' => function ($query) {
                    $query->select('clubs.id', 'clubs.name', 'clubs.address', 'clubs.city')
                          ->orderBy('trainer_clubs.is_primary', 'desc');
                },
                'availability'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Trainer profile updated successfully',
                'data' => [
                    'trainer_profile' => $trainerProfile
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update trainer profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified trainer profile.
     */
    public function destroy(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        // Only admins and owners can delete trainer profiles
        if (!in_array($request->user()->user_role, ['admin', 'owner'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to delete trainer profiles'
            ], 403);
        }

        try {
            $trainerProfile->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Trainer profile deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete trainer profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify a trainer profile.
     */
    public function verify(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        // Only admins and owners can verify trainers
        if (!in_array($request->user()->user_role, ['admin', 'owner'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to verify trainer profiles'
            ], 403);
        }

        $trainerProfile->update(['is_verified' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Trainer profile verified successfully',
            'data' => [
                'trainer_profile' => $trainerProfile
            ]
        ]);
    }

    /**
     * Unverify a trainer profile.
     */
    public function unverify(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        // Only admins and owners can unverify trainers
        if (!in_array($request->user()->user_role, ['admin', 'owner'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to unverify trainer profiles'
            ], 403);
        }

        $trainerProfile->update(['is_verified' => false]);

        return response()->json([
            'status' => 'success',
            'message' => 'Trainer profile unverified successfully',
            'data' => [
                'trainer_profile' => $trainerProfile
            ]
        ]);
    }

    /**
     * Toggle trainer availability.
     */
    public function toggleAvailability(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        // Authorization check
        if (!in_array($request->user()->user_role, ['admin', 'owner']) && 
            $trainerProfile->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to change this trainer\'s availability'
            ], 403);
        }

        $trainerProfile->update(['is_available' => !$trainerProfile->is_available]);

        return response()->json([
            'status' => 'success',
            'message' => 'Trainer availability updated successfully',
            'data' => [
                'trainer_profile' => $trainerProfile
            ]
        ]);
    }

    /**
     * Get trainers for a specific sport.
     */
    public function getBySport(Request $request, Sport $sport): JsonResponse
    {
        $query = $sport->trainerProfiles()->with([
            'user:id,name,email,phone,gender,profile_image_url,join_date',
            'tier:id,tier_name,display_name,price,features',
            'specialties' => function ($query) {
                $query->orderBy('specialty', 'asc');
            },
            'certifications' => function ($query) {
                $query->where('is_verified', true)
                      ->orderBy('certification_name', 'asc');
            },
            'locations' => function ($query) use ($request) {
                // Show all locations by default, or filter by is_primary if requested
                if ($request->boolean('primary_locations_only')) {
                    $query->where('is_primary', true);
                }
                $query->orderBy('is_primary', 'desc')
                      ->orderBy('location_type', 'asc');
            },
            'clubs' => function ($query) {
                $query->select('clubs.id', 'clubs.name', 'clubs.address', 'clubs.city')
                      ->orderBy('trainer_clubs.is_primary', 'desc');
            },
            'availability' => function ($query) {
                $query->where('is_available', true)
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
                      ->orderBy('start_time', 'asc');
            }
        ]);

        // Role-based filtering
        $user = $request->user();
        if (!$user || !in_array($user->user_role, ['admin', 'owner'])) {
            // Regular users and unauthenticated users can only see verified and available trainers
            $query->verified()->available();
        } else {
            // Filter by verification status for admins/owners
            if ($request->has('verified')) {
                $query->where('is_verified', $request->boolean('verified'));
            }

            // Filter by availability for admins/owners
            if ($request->has('available')) {
                $query->where('is_available', $request->boolean('available'));
            }
        }

        // Filter by active trainers (verified and available)
        if ($request->boolean('active')) {
            $query->active();
        }

        // Filter by rating range
        if ($request->filled('min_rating') || $request->filled('max_rating')) {
            $query->byRating($request->min_rating, $request->max_rating);
        }

        // Filter by experience range
        if ($request->filled('min_experience') || $request->filled('max_experience')) {
            $query->byExperience($request->min_experience, $request->max_experience);
        }

        // Filter by gender preference
        if ($request->filled('gender_preference')) {
            $query->byGenderPreference($request->gender_preference);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'ILIKE', "%{$search}%")
                             ->orWhere('email', 'ILIKE', "%{$search}%");
                })
                ->orWhere('bio', 'ILIKE', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'rating');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Handle sorting by related fields
        if ($sortBy === 'user_name') {
            $query->join('users', 'trainer_profiles.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortOrder)
                  ->select('trainer_profiles.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $trainers = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'sport' => $sport->only(['id', 'name', 'display_name', 'icon', 'color']),
                'trainers' => $trainers->items(),
                'pagination' => [
                    'current_page' => $trainers->currentPage(),
                    'last_page' => $trainers->lastPage(),
                    'per_page' => $trainers->perPage(),
                    'total' => $trainers->total(),
                ]
            ]
        ]);
    }

    /**
     * Get trainer statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = TrainerProfile::query();

        // Role-based filtering
        if (!in_array($request->user()->user_role, ['admin', 'owner'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to view trainer statistics'
            ], 403);
        }

        $stats = [
            'total_trainers' => $query->count(),
            'verified_trainers' => $query->verified()->count(),
            'available_trainers' => $query->available()->count(),
            'active_trainers' => $query->active()->count(),
            'average_rating' => round($query->where('rating', '>', 0)->avg('rating') ?? 0, 2),
            'total_sessions' => $query->sum('total_sessions'),
            'total_earnings' => $query->sum('total_earnings'),
        ];

        // Sports breakdown
        $stats['sports_breakdown'] = TrainerProfile::join('sports', 'trainer_profiles.sport_id', '=', 'sports.id')
            ->selectRaw('sports.name, sports.display_name, COUNT(*) as trainer_count, AVG(trainer_profiles.rating) as avg_rating')
            ->where('trainer_profiles.is_verified', true)
            ->groupBy('sports.id', 'sports.name', 'sports.display_name')
            ->get();

        // Experience level breakdown
        $stats['experience_breakdown'] = [
            'beginner' => $query->where('experience_years', '<', 2)->count(),
            'intermediate' => $query->whereBetween('experience_years', [2, 4])->count(),
            'senior' => $query->whereBetween('experience_years', [5, 9])->count(),
            'expert' => $query->where('experience_years', '>=', 10)->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'statistics' => $stats
            ]
        ]);
    }

    /**
     * Get user's trainer profile.
     */
    public function myProfile(Request $request): JsonResponse
    {
        $trainerProfile = TrainerProfile::with([
            'sport:id,name,display_name,icon,color',
            'tier:id,tier_name,display_name,price,features',
            'specialties',
            'certifications',
            'locations',
            'clubs' => function ($query) {
                $query->select('clubs.id', 'clubs.name', 'clubs.address', 'clubs.city')
                      ->orderBy('trainer_clubs.is_primary', 'desc');
            },
            'availability'
        ])->where('user_id', $request->user()->id)->first();

        if (!$trainerProfile) {
            return response()->json([
                'status' => 'error',
                'message' => 'No trainer profile found for this user'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'trainer_profile' => $trainerProfile
            ]
        ]);
    }

    /**
     * Update trainer statistics (to be called periodically).
     */
    public function updateStatistics(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        // Only admins, owners, or the trainer themselves can update statistics
        if (!in_array($request->user()->user_role, ['admin', 'owner']) && 
            $trainerProfile->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to update trainer statistics'
            ], 403);
        }

        $trainerProfile->updateStatistics();

        return response()->json([
            'status' => 'success',
            'message' => 'Trainer statistics updated successfully',
            'data' => [
                'trainer_profile' => $trainerProfile->fresh()
            ]
        ]);
    }

    /**
     * Add a club to the trainer's profile.
     */
    public function addClub(Request $request, TrainerProfile $trainerProfile): JsonResponse
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

        $trainerProfile->clubs()->attach($request->club_id, [
            'is_primary' => $request->is_primary ?? false
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Club added to trainer successfully'
        ]);
    }

    /**
     * Remove a club from the trainer's profile.
     */
    public function removeClub(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        $request->validate([
            'club_id' => 'required|uuid|exists:clubs,id'
        ]);

        $trainerProfile->clubs()->detach($request->club_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Club removed from trainer successfully'
        ]);
    }
}
