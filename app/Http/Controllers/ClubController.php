<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Sport;
use App\Models\Amenity;
use App\Models\Facility;
use App\Models\ClubImage;
use App\Models\CheckIn;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Requests\StoreClubRequest;
use App\Http\Requests\UpdateClubRequest;

class ClubController extends Controller
{
    /**
     * List clubs with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Club::with(['owner', 'sports', 'amenities', 'facilities', 'primaryImage']);

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by verification status
        if ($request->filled('verification_status')) {
            $query->byVerificationStatus($request->verification_status);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', 'ILIKE', "%{$request->city}%");
        }

        // Nearby clubs
        if ($request->filled(['latitude', 'longitude'])) {
            $radius = $request->get('radius', 10); // Default 10km
            $query->withinRadius($request->latitude, $request->longitude, $radius);
        }

        // Filter by owner
        if ($request->filled('owner_id')) {
            $query->ownedBy($request->owner_id);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        if ($sortBy === 'distance' && $request->filled(['latitude', 'longitude'])) {
            // Distance sorting is already handled in withinRadius scope
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $clubs = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'clubs' => $clubs
            ]
        ]);
    }

    /**
     * Store a new club.
     */
    public function store(StoreClubRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $club = new Club($request->validated());

            $club->owner_id = $request->user()->id;
            $club->qr_code = $club->generateQrCode();
            $club->status = 'pending';
            $club->verification_status = 'pending';
            $club->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Club created successfully',
                'data' => [
                    'club' => $club->load(['owner', 'sports', 'amenities', 'facilities'])
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create club',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific club.
     */
    public function show(Club $club): JsonResponse
    {
        $club->load([
            'owner',
            'sports',
            'amenities',
            'facilities',
            'images',
            'checkIns' => function ($query) {
                $query->latest()->limit(10);
            },
            'events' => function ($query) {
                $query->where('event_date', '>=', now()->toDateString())
                      ->orderBy('event_date')
                      ->limit(5);
            }
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'club' => $club,
                'statistics' => $club->getStatistics()
            ]
        ]);
    }

    /**
     * Update a club.
     */
    public function update(UpdateClubRequest $request, Club $club): JsonResponse
    {
        // Check if user is owner or admin
        if ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only update your own clubs'
            ], 403);
        }

        try {
            $club->update($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Club updated successfully',
                'data' => [
                    'club' => $club->fresh(['owner', 'sports', 'amenities', 'facilities'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update club',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a club.
     */
    public function destroy(Request $request, Club $club): JsonResponse
    {
        // Check if user is owner or admin
        if ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only delete your own clubs'
            ], 403);
        }

        try {
            $club->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Club deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete club',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get clubs owned by the authenticated user.
     */
    public function myClubs(Request $request): JsonResponse
    {
        $clubs = Club::ownedBy($request->user()->id)
            ->with(['sports', 'amenities', 'facilities', 'primaryImage'])
            ->orderBy('name')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => [
                'clubs' => $clubs
            ]
        ]);
    }

    /**
     * Update club status (admin only).
     */
    public function updateStatus(Request $request, Club $club): JsonResponse
    {
        if ($request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only admins can update club status'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,pending,suspended'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $club->update(['status' => $request->status]);

            return response()->json([
                'status' => 'success',
                'message' => 'Club status updated successfully',
                'data' => [
                    'club' => $club->fresh()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update club status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle club active status (owner or admin only).
     */
    public function toggleStatus(Request $request, Club $club): JsonResponse
    {
        // Check if user is owner or admin
        if ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only toggle status for your own clubs'
            ], 403);
        }

        try {
            $club->update(['is_active' => !$club->is_active]);

            return response()->json([
                'status' => 'success',
                'message' => 'Club status updated successfully',
                'data' => [
                    'club' => $club->fresh()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to toggle club status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update club verification status (admin only).
     */
    public function updateVerificationStatus(Request $request, Club $club): JsonResponse
    {
        if ($request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only admins can update verification status'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'verification_status' => 'required|in:pending,verified,rejected'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $club->update(['verification_status' => $request->verification_status]);

            return response()->json([
                'status' => 'success',
                'message' => 'Club verification status updated successfully',
                'data' => [
                    'club' => $club->fresh()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update verification status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add sports to a club.
     */
    public function addSports(Request $request, Club $club): JsonResponse
    {
        if ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only manage your own clubs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'sport_ids' => 'required|array',
            'sport_ids.*' => 'uuid|exists:sports,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $club->sports()->syncWithoutDetaching($request->sport_ids);

            return response()->json([
                'status' => 'success',
                'message' => 'Sports added to club successfully',
                'data' => [
                    'club' => $club->fresh(['sports'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add sports',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove sports from a club.
     */
    public function removeSports(Request $request, Club $club): JsonResponse
    {
        if ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only manage your own clubs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'sport_ids' => 'required|array',
            'sport_ids.*' => 'uuid|exists:sports,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $club->sports()->detach($request->sport_ids);

            return response()->json([
                'status' => 'success',
                'message' => 'Sports removed from club successfully',
                'data' => [
                    'club' => $club->fresh(['sports'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove sports',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add amenities to a club.
     */
    public function addAmenities(Request $request, Club $club): JsonResponse
    {
        if ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only manage your own clubs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'amenity_ids' => 'required|array',
            'amenity_ids.*' => 'uuid|exists:amenities,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $club->amenities()->syncWithoutDetaching($request->amenity_ids);

            return response()->json([
                'status' => 'success',
                'message' => 'Amenities added to club successfully',
                'data' => [
                    'club' => $club->fresh(['amenities'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add amenities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove amenities from a club.
     */
    public function removeAmenities(Request $request, Club $club): JsonResponse
    {
        if ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only manage your own clubs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'amenity_ids' => 'required|array',
            'amenity_ids.*' => 'uuid|exists:amenities,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $club->amenities()->detach($request->amenity_ids);

            return response()->json([
                'status' => 'success',
                'message' => 'Amenities removed from club successfully',
                'data' => [
                    'club' => $club->fresh(['amenities'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove amenities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add facilities to a club.
     */
    public function addFacilities(Request $request, Club $club): JsonResponse
    {
        if ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only manage your own clubs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'facility_ids' => 'required|array',
            'facility_ids.*' => 'uuid|exists:facilities,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $club->facilities()->syncWithoutDetaching($request->facility_ids);

            return response()->json([
                'status' => 'success',
                'message' => 'Facilities added to club successfully',
                'data' => [
                    'club' => $club->fresh(['facilities'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add facilities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove facilities from a club.
     */
    public function removeFacilities(Request $request, Club $club): JsonResponse
    {
        if ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only manage your own clubs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'facility_ids' => 'required|array',
            'facility_ids.*' => 'uuid|exists:facilities,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $club->facilities()->detach($request->facility_ids);

            return response()->json([
                'status' => 'success',
                'message' => 'Facilities removed from club successfully',
                'data' => [
                    'club' => $club->fresh(['facilities'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove facilities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get club statistics.
     */
    public function statistics(Club $club): JsonResponse
    {
        // Check if user is owner or admin
        if (auth()->check() && (auth()->user()->id !== $club->owner_id && auth()->user()->user_role !== 'admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only view statistics for your own clubs'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'club' => $club,
                'statistics' => $club->getStatistics()
            ]
        ]);
    }

    /**
     * Generate new QR code for club.
     */
    public function generateQrCode(Request $request, Club $club): JsonResponse
    {
        if ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only manage your own clubs'
            ], 403);
        }

        try {
            $newQrCode = $club->generateQrCode();
            $club->update(['qr_code' => $newQrCode]);

            return response()->json([
                'status' => 'success',
                'message' => 'QR code generated successfully',
                'data' => [
                    'qr_code' => $newQrCode
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate QR code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if club is open at a specific time.
     */
    public function isOpen(Request $request, Club $club): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'day_of_week' => 'required|integer|between:0,6',
            'time' => 'required|date_format:H:i'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $isOpen = $club->isOpenAt($request->day_of_week, $request->time);

        return response()->json([
            'status' => 'success',
            'data' => [
                'is_open' => $isOpen,
                'club' => $club->only(['name', 'timings'])
            ]
        ]);
    }

    /**
     * Get nearby clubs.
     */
    public function nearby(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $radius = $request->get('radius', 10);

        $clubs = Club::active()
            ->withinRadius($request->latitude, $request->longitude, $radius)
            ->with(['sports', 'amenities', 'facilities', 'primaryImage'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'clubs' => $clubs,
                'search_location' => [
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'radius' => $radius
                ]
            ]
        ]);
    }

    /**
     * Verify a club (Admin only).
     */
    public function verify(Request $request, Club $club): JsonResponse
    {
        // Check if user is admin
        if ($request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only administrators can verify clubs'
            ], 403);
        }

        if ($club->verification_status === 'verified') {
            return response()->json([
                'status' => 'error',
                'message' => 'Club is already verified'
            ], 422);
        }

        $club->update([
            'verification_status' => 'verified',
            'status' => 'active'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Club verified successfully',
            'data' => [
                'club' => $club->fresh(['owner', 'sports', 'amenities', 'facilities'])
            ]
        ]);
    }

    /**
     * Unverify a club (Admin only).
     */
    public function unverify(Request $request, Club $club): JsonResponse
    {
        // Check if user is admin
        if ($request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only administrators can unverify clubs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($club->verification_status === 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Club is already unverified'
            ], 422);
        }

        $club->update([
            'verification_status' => 'pending',
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Club unverified successfully',
            'data' => [
                'club' => $club->fresh(['owner', 'sports', 'amenities', 'facilities'])
            ]
        ]);
    }

    /**
     * Get admin club statistics.
     */
    public function adminStatistics(Request $request): JsonResponse
    {
        // Check if user is admin
        if ($request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only administrators can access club statistics'
            ], 403);
        }

        $stats = [
            'total_clubs' => Club::count(),
            'verified_clubs' => Club::where('verification_status', 'verified')->count(),
            'unverified_clubs' => Club::where('verification_status', 'pending')->count(),
            'rejected_clubs' => Club::where('verification_status', 'rejected')->count(),
            'active_clubs' => Club::where('is_active', true)->count(),
            'inactive_clubs' => Club::where('is_active', false)->count(),
            'clubs_by_city' => Club::select('city', DB::raw('count(*) as count'))
                ->whereNotNull('city')
                ->groupBy('city')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->pluck('count', 'city'),
            'clubs_by_category' => Club::select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->pluck('count', 'category'),
            'average_rating' => Club::where('rating', '>', 0)->avg('rating'),
            'top_performing_clubs' => Club::select('id', 'name', 'rating', 'city')
                ->where('rating', '>', 0)
                ->orderBy('rating', 'desc')
                ->limit(5)
                ->get()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * Get clubs for admin management.
     */
    public function adminIndex(Request $request): JsonResponse
    {
        // Check if user is admin
        if ($request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only administrators can access club management'
            ], 403);
        }

        $query = Club::with(['owner', 'sports', 'amenities', 'facilities', 'primaryImage']);

        // Filter by verification status
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by owner
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $clubs = $query->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $clubs
        ]);
    }

    /**
     * Filter clubs with advanced filtering options.
     */
    public function filter(Request $request): JsonResponse
    {
        $query = Club::with(['owner', 'sports', 'amenities', 'facilities', 'primaryImage']);

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by verification status
        if ($request->filled('verification_status')) {
            $query->byVerificationStatus($request->verification_status);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', 'ILIKE', "%{$request->city}%");
        }

        // Filter by sports
        if ($request->filled('sport_ids')) {
            $sportIds = is_array($request->sport_ids) ? $request->sport_ids : [$request->sport_ids];
            $query->whereHas('sports', function ($q) use ($sportIds) {
                $q->whereIn('sports.id', $sportIds);
            });
        }

        // Filter by amenities
        if ($request->filled('amenity_ids')) {
            $amenityIds = is_array($request->amenity_ids) ? $request->amenity_ids : [$request->amenity_ids];
            $query->whereHas('amenities', function ($q) use ($amenityIds) {
                $q->whereIn('amenities.id', $amenityIds);
            });
        }

        // Filter by facilities
        if ($request->filled('facility_ids')) {
            $facilityIds = is_array($request->facility_ids) ? $request->facility_ids : [$request->facility_ids];
            $query->whereHas('facilities', function ($q) use ($facilityIds) {
                $q->whereIn('facilities.id', $facilityIds);
            });
        }

        // Filter by price range
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('sports.tiers', function ($q) use ($request) {
                if ($request->filled('min_price')) {
                    $q->where('price', '>=', $request->min_price);
                }
                if ($request->filled('max_price')) {
                    $q->where('price', '<=', $request->max_price);
                }
            });
        }

        // Filter by rating
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        // Nearby clubs
        if ($request->filled(['latitude', 'longitude'])) {
            $radius = $request->get('radius', 10);
            $query->withinRadius($request->latitude, $request->longitude, $radius);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        if ($sortBy === 'distance' && $request->filled(['latitude', 'longitude'])) {
            // Distance sorting is already handled in withinRadius scope
        } elseif ($sortBy === 'rating') {
            $query->orderBy('rating', $sortDirection);
        } elseif ($sortBy === 'price') {
            $query->leftJoin('club_sports', 'clubs.id', '=', 'club_sports.club_id')
                  ->leftJoin('sport_tiers', 'club_sports.sport_id', '=', 'sport_tiers.sport_id')
                  ->orderBy('sport_tiers.price', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $clubs = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'clubs' => $clubs,
                'filters_applied' => [
                    'active' => $request->has('active'),
                    'status' => $request->filled('status'),
                    'verification_status' => $request->filled('verification_status'),
                    'category' => $request->filled('category'),
                    'city' => $request->filled('city'),
                    'sports' => $request->filled('sport_ids'),
                    'amenities' => $request->filled('amenity_ids'),
                    'facilities' => $request->filled('facility_ids'),
                    'price_range' => $request->filled(['min_price', 'max_price']),
                    'rating' => $request->filled('min_rating'),
                    'location' => $request->filled(['latitude', 'longitude'])
                ]
            ]
        ]);
    }

    /**
     * Search clubs with text-based search.
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:100',
            'limit' => 'nullable|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = $request->get('query');
        $limit = $request->get('limit', 20);

        $clubs = Club::search($query)
            ->with(['owner', 'sports', 'amenities', 'facilities', 'primaryImage'])
            ->where('is_active', true)
            ->limit($limit)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'clubs' => $clubs,
                'search_query' => $query,
                'total_results' => $clubs->count()
            ]
        ]);
    }

    /**
     * Get sports associated with a club.
     */
    public function getSports(Club $club): JsonResponse
    {
        $sports = $club->sports()
            ->with(['tiers' => function ($query) {
                $query->active()->available();
            }])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'club' => $club->only(['id', 'name']),
                'sports' => $sports
            ]
        ]);
    }

    /**
     * Remove a specific sport from a club.
     */
    public function removeSport(Request $request, Club $club, Sport $sport): JsonResponse
    {
        if ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only manage your own clubs'
            ], 403);
        }

        if (!$club->sports()->where('sport_id', $sport->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sport is not associated with this club'
            ], 404);
        }

        try {
            $club->sports()->detach($sport->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Sport removed from club successfully',
                'data' => [
                    'club' => $club->fresh(['sports'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove sport',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get amenities associated with a club.
     */
    public function getAmenities(Club $club): JsonResponse
    {
        $amenities = $club->amenities()
            ->withPivot('custom_name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'club' => $club->only(['id', 'name']),
                'amenities' => $amenities
            ]
        ]);
    }

    /**
     * Remove a specific amenity from a club.
     */
    public function removeAmenity(Request $request, Club $club, Amenity $amenity): JsonResponse
    {
        if ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only manage your own clubs'
            ], 403);
        }

        if (!$club->amenities()->where('amenity_id', $amenity->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Amenity is not associated with this club'
            ], 404);
        }

        try {
            $club->amenities()->detach($amenity->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Amenity removed from club successfully',
                'data' => [
                    'club' => $club->fresh(['amenities'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove amenity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get facilities associated with a club.
     */
    public function getFacilities(Club $club): JsonResponse
    {
        $facilities = $club->facilities()
            ->withPivot('custom_name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'club' => $club->only(['id', 'name']),
                'facilities' => $facilities
            ]
        ]);
    }

    /**
     * Remove a specific facility from a club.
     */
    public function removeFacility(Request $request, Club $club, Facility $facility): JsonResponse
    {
        if ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only manage your own clubs'
            ], 403);
        }

        if (!$club->facilities()->where('facility_id', $facility->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Facility is not associated with this club'
            ], 404);
        }

        try {
            $club->facilities()->detach($facility->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Facility removed from club successfully',
                'data' => [
                    'club' => $club->fresh(['facilities'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove facility',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get images associated with a club.
     */
    public function getImages(Club $club): JsonResponse
    {
        $images = ClubImage::where('club_id', $club->id)
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'club' => $club->only(['id', 'name']),
                'images' => $images
            ]
        ]);
    }

    /**
     * Add an image to a club.
     */
    public function addImage(Request $request, Club $club): JsonResponse
    {
        if ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only manage your own clubs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'alt_text' => 'nullable|string|max:255',
            'is_primary' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle image upload
            $imagePath = $request->file('image')->store('club-images', 'public');

            // If setting as primary, unset other primary images
            if ($request->boolean('is_primary', false)) {
                $club->images()->update(['is_primary' => false]);
            }

            // Get next sort order
            $nextSortOrder = $club->images()->max('display_order') + 1;

            $image = $club->images()->create([
                'image_url' => $imagePath,
                'alt_text' => $request->alt_text,
                'is_primary' => $request->boolean('is_primary', false),
                'display_order' => $nextSortOrder
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Image added to club successfully',
                'data' => [
                    'image' => $image
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove an image from a club.
     */
    public function removeImage(Request $request, Club $club, ClubImage $clubImage): JsonResponse
    {
        if ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only manage your own clubs'
            ], 403);
        }

        if ($clubImage->club_id !== $club->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Image does not belong to this club'
            ], 404);
        }

        try {
            // Delete the image file
            if (file_exists(storage_path('app/public/' . $clubImage->image_url))) {
                unlink(storage_path('app/public/' . $clubImage->image_url));
            }

            $clubImage->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Image removed from club successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check in to a club.
     */
    public function checkIn(Request $request, Club $club): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'membership_id' => 'required|uuid|exists:memberships,id',
            'check_in_method' => 'nullable|in:manual,qr_code,mobile_app',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verify membership belongs to user and is valid
            $membership = $request->user()->memberships()
                ->where('id', $request->membership_id)
                ->where('status', 'active')
                ->first();

            if (!$membership) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or inactive membership'
                ], 422);
            }

            // Check if membership allows access to this club
            if (!$membership->canAccessClub($club->id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your membership does not allow access to this club'
                ], 403);
            }

            // Check if already checked in and not checked out
            $existingCheckIn = CheckIn::where('user_id', $request->user()->id)
                ->where('club_id', $club->id)
                ->whereNull('check_out_time')
                ->first();

            if ($existingCheckIn) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are already checked in to this club'
                ], 422);
            }

            $checkIn = CheckIn::create([
                'user_id' => $request->user()->id,
                'club_id' => $club->id,
                'membership_id' => $request->membership_id,
                'check_in_time' => now(),
                'check_in_method' => $request->get('check_in_method', 'mobile_app'),
                'notes' => $request->notes
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Checked in successfully',
                'data' => [
                    'check_in' => $checkIn->load(['club', 'membership'])
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check in',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get check-ins for a club.
     */
    public function getCheckIns(Request $request, Club $club): JsonResponse
    {
        // Check if user is owner or admin
        if ($request->user() && ($request->user()->id !== $club->owner_id && $request->user()->user_role !== 'admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only view check-ins for your own clubs'
            ], 403);
        }

        $query = $club->checkIns()
            ->with([
                'user:id,name,email',
                'membership:id,status,tier_id',
                'membership.tier:id,tier_name,display_name'
            ]);

        // Filter by date range
        if ($request->filled(['start_date', 'end_date'])) {
            $query->whereBetween('check_in_time', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('check_out_time');
            } elseif ($request->status === 'completed') {
                $query->whereNotNull('check_out_time');
            }
        }

        $checkIns = $query->orderBy('check_in_time', 'desc')->paginate(15);

        // Transform the data to handle null relationships safely
        $transformedCheckIns = $checkIns->getCollection()->map(function ($checkIn) {
            return [
                'id' => $checkIn->id,
                'user_id' => $checkIn->user_id,
                'club_id' => $checkIn->club_id,
                'membership_id' => $checkIn->membership_id,
                'check_in_date' => $checkIn->check_in_date,
                'check_in_time' => $checkIn->check_in_time,
                'check_out_time' => $checkIn->check_out_time,
                'sport_type' => $checkIn->sport_type,
                'qr_code_used' => $checkIn->qr_code_used,
                'duration_minutes' => $checkIn->duration_minutes,
                'notes' => $checkIn->notes,
                'created_at' => $checkIn->created_at,
                'updated_at' => $checkIn->updated_at,
                'user' => $checkIn->user ? [
                    'id' => $checkIn->user->id,
                    'name' => $checkIn->user->name,
                    'email' => $checkIn->user->email,
                ] : null,
                'membership' => $checkIn->membership ? [
                    'id' => $checkIn->membership->id,
                    'membership_type' => $checkIn->membership->tier ? $checkIn->membership->tier->display_name : null,
                    'status' => $checkIn->membership->status,
                ] : null,
            ];
        });

        $checkIns->setCollection($transformedCheckIns);

        return response()->json([
            'status' => 'success',
            'data' => [
                'club' => $club->only(['id', 'name']),
                'check_ins' => $checkIns
            ]
        ]);
    }

    /**
     * Get events associated with a club.
     */
    public function getEvents(Request $request, Club $club): JsonResponse
    {
        $query = $club->events()->with(['sport', 'registrations']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('event_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('event_date', '<=', $request->end_date);
        }

        // Only upcoming events by default
        if (!$request->filled('include_past')) {
            $query->where('event_date', '>=', now()->toDateString());
        }

        $events = $query->orderBy('event_date')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => [
                'club' => $club->only(['id', 'name']),
                'events' => $events
            ]
        ]);
    }
}
