<?php

namespace App\Http\Controllers;

use App\Models\TrainerLocation;
use App\Models\TrainerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TrainerLocationController extends Controller
{
    /**
     * Display a listing of locations.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TrainerLocation::with(['trainerProfile.user:id,name,email']);

        // Filter by trainer profile
        if ($request->filled('trainer_profile_id')) {
            $query->where('trainer_profile_id', $request->trainer_profile_id);
        }

        // Filter by location type
        if ($request->filled('location_type')) {
            $query->where('location_type', $request->location_type);
        }

        // Filter by city/area
        if ($request->filled('city')) {
            $query->where('city', 'ILIKE', '%' . $request->city . '%');
        }

        if ($request->filled('area')) {
            $query->where('area', 'ILIKE', '%' . $request->area . '%');
        }

        // Geographic search within radius
        if ($request->filled(['latitude', 'longitude', 'radius'])) {
            $lat = $request->get('latitude');
            $lng = $request->get('longitude');
            $radius = $request->get('radius', 10); // Default 10km

            $query->withinRadius($lat, $lng, $radius);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'location_name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if ($sortBy === 'distance' && $request->filled(['latitude', 'longitude'])) {
            $lat = $request->get('latitude');
            $lng = $request->get('longitude');
            
            // Get locations and calculate distance using Haversine formula
            $locations = $query->get();
            
            $locations = $locations->map(function ($location) use ($lat, $lng) {
                $location->distance_km = round($location->distanceTo($lat, $lng), 2);
                return $location;
            })->sortBy('distance_km');
            
            if ($sortOrder === 'desc') {
                $locations = $locations->sortByDesc('distance_km');
            }
            
            // Convert back to paginated format
            $perPage = $request->get('per_page', 15);
            $currentPage = $request->get('page', 1);
            $total = $locations->count();
            $items = $locations->forPage($currentPage, $perPage)->values();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'locations' => $items,
                    'pagination' => [
                        'current_page' => $currentPage,
                        'last_page' => ceil($total / $perPage),
                        'per_page' => $perPage,
                        'total' => $total,
                    ]
                ]
            ]);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $locations = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'locations' => $locations->items(),
                'pagination' => [
                    'current_page' => $locations->currentPage(),
                    'last_page' => $locations->lastPage(),
                    'per_page' => $locations->perPage(),
                    'total' => $locations->total(),
                ]
            ]
        ]);
    }

    /**
     * Store a newly created location.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'trainer_profile_id' => 'required|exists:trainer_profiles,id',
            'location_name' => 'required|string|max:255',
            'location_type' => 'required|in:gym,outdoor,home,client_location,online',
            'address' => 'nullable|string|max:500',
            'city' => 'required|string|max:100',
            'area' => 'nullable|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'is_primary' => 'boolean',
        ]);

        // Authorization check
        $trainerProfile = TrainerProfile::findOrFail($request->trainer_profile_id);
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && $trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to add locations for this trainer'
            ], 403);
        }

        // Check if setting as primary and update existing primary if needed
        if ($request->get('is_primary', false)) {
            TrainerLocation::where('trainer_profile_id', $request->trainer_profile_id)
                          ->where('is_primary', true)
                          ->update(['is_primary' => false]);
        }

        $location = TrainerLocation::create($request->all());
        $location->load(['trainerProfile.user:id,name,email']);

        return response()->json([
            'status' => 'success',
            'message' => 'Location added successfully',
            'data' => [
                'location' => $location
            ]
        ], 201);
    }

    /**
     * Display the specified location.
     */
    public function show(Request $request, TrainerLocation $trainerLocation): JsonResponse
    {
        $trainerLocation->load(['trainerProfile.user:id,name,email']);

        // Calculate distance if user location provided
        if ($request->filled(['latitude', 'longitude'])) {
            $distance = $trainerLocation->distanceTo(
                $request->get('latitude'),
                $request->get('longitude')
            );
            $trainerLocation->distance_km = round($distance, 2);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'location' => $trainerLocation
            ]
        ]);
    }

    /**
     * Update the specified location.
     */
    public function update(Request $request, TrainerLocation $trainerLocation): JsonResponse
    {
        $request->validate([
            'location_name' => 'sometimes|string|max:255',
            'location_type' => 'sometimes|in:gym,outdoor,home,client_location,online',
            'address' => 'nullable|string|max:500',
            'city' => 'sometimes|string|max:100',
            'area' => 'nullable|string|max:100',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'is_primary' => 'sometimes|boolean',
        ]);

        // Authorization check
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && $trainerLocation->trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to update this location'
            ], 403);
        }

        // Check if setting as primary and update existing primary if needed
        if ($request->get('is_primary', false)) {
            TrainerLocation::where('trainer_profile_id', $trainerLocation->trainer_profile_id)
                          ->where('id', '!=', $trainerLocation->id)
                          ->where('is_primary', true)
                          ->update(['is_primary' => false]);
        }

        $trainerLocation->update($request->only([
            'location_name', 'location_type', 'address', 'city', 'area', 
            'latitude', 'longitude', 'is_primary'
        ]));
        
        $trainerLocation->load(['trainerProfile.user:id,name,email']);

        return response()->json([
            'status' => 'success',
            'message' => 'Location updated successfully',
            'data' => [
                'location' => $trainerLocation
            ]
        ]);
    }

    /**
     * Remove the specified location.
     */
    public function destroy(Request $request, TrainerLocation $trainerLocation): JsonResponse
    {
        // Authorization check
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && $trainerLocation->trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to delete this location'
            ], 403);
        }

        // If deleting primary location, make another location primary if exists
        if ($trainerLocation->is_primary) {
            $nextPrimary = TrainerLocation::where('trainer_profile_id', $trainerLocation->trainer_profile_id)
                                        ->where('id', '!=', $trainerLocation->id)
                                        ->first();
            
            if ($nextPrimary) {
                $nextPrimary->update(['is_primary' => true]);
            }
        }

        $trainerLocation->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Location deleted successfully'
        ]);
    }

    /**
     * Get locations for a specific trainer.
     */
    public function getByTrainer(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        $query = $trainerProfile->locations();

        // Filter by location type if requested
        if ($request->filled('location_type')) {
            $query->where('location_type', $request->location_type);
        }

        $locations = $query->orderBy('is_primary', 'desc')
                          ->orderBy('location_name', 'asc')
                          ->get();

        // Calculate distances if user location provided
        if ($request->filled(['latitude', 'longitude'])) {
            $userLat = $request->get('latitude');
            $userLng = $request->get('longitude');
            
            $locations->each(function ($location) use ($userLat, $userLng) {
                $distance = $location->distanceTo($userLat, $userLng);
                $location->distance_km = round($distance, 2);
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'trainer_profile' => $trainerProfile->only(['id', 'user_id']),
                'locations' => $locations
            ]
        ]);
    }

    /**
     * Find trainers near a location.
     */
    public function findNearby(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'sometimes|numeric|min:1|max:100',
            'location_type' => 'sometimes|in:gym,outdoor,home,client_location,online',
        ]);

        $lat = $request->get('latitude');
        $lng = $request->get('longitude');
        $radius = $request->get('radius', 10); // Default 10km

        $query = TrainerLocation::with(['trainerProfile.user:id,name,email'])
                               ->withinRadius($lat, $lng, $radius);

        if ($request->filled('location_type')) {
            $query->where('location_type', $request->location_type);
        }

        // Get locations and calculate distances using Haversine formula
        $locations = $query->get();
        
        $locations = $locations->map(function ($location) use ($lat, $lng) {
            $location->distance_km = round($location->distanceTo($lat, $lng), 2);
            return $location;
        })->sortBy('distance_km')
          ->take($request->get('limit', 20))
          ->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'search_center' => [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'radius_km' => $radius
                ],
                'nearby_locations' => $locations,
                'count' => $locations->count()
            ]
        ]);
    }

    /**
     * Set primary location for a trainer.
     */
    public function setPrimary(Request $request, TrainerLocation $trainerLocation): JsonResponse
    {
        // Authorization check
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && $trainerLocation->trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to update this location'
            ], 403);
        }

        // Update all locations for this trainer to not primary
        TrainerLocation::where('trainer_profile_id', $trainerLocation->trainer_profile_id)
                      ->update(['is_primary' => false]);

        // Set this location as primary
        $trainerLocation->update(['is_primary' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Primary location updated successfully',
            'data' => [
                'location' => $trainerLocation
            ]
        ]);
    }

    /**
     * Get location statistics.
     */
    public function getStats(Request $request): JsonResponse
    {
        $stats = [
            'total_locations' => TrainerLocation::count(),
            'by_type' => TrainerLocation::selectRaw('location_type, COUNT(*) as count')
                                       ->groupBy('location_type')
                                       ->pluck('count', 'location_type'),
            'by_city' => TrainerLocation::selectRaw('city, COUNT(*) as count')
                                       ->groupBy('city')
                                       ->orderBy('count', 'desc')
                                       ->limit(10)
                                       ->pluck('count', 'city'),
            'primary_locations' => TrainerLocation::where('is_primary', true)->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'statistics' => $stats
            ]
        ]);
    }
}
