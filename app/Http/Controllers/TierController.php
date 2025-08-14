<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Models\Tier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TierController extends Controller
{
    /**
     * Display a listing of tiers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tier::with('sport:id,name,display_name');

        // Filter by sport
        if ($request->filled('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Filter by available (within date range and active)
        if ($request->boolean('available')) {
            $query->active()->available();
        }

        // Filter by price range
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('tier_name', 'ILIKE', "%{$search}%")
                  ->orWhere('display_name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'tier_name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $tiers = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'tiers' => $tiers->items(),
                'pagination' => [
                    'current_page' => $tiers->currentPage(),
                    'last_page' => $tiers->lastPage(),
                    'per_page' => $tiers->perPage(),
                    'total' => $tiers->total(),
                ]
            ]
        ]);
    }

    /**
     * Store a newly created tier.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sport_id' => 'required|exists:sports,id',
            'tier_name' => 'required|string|max:50',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'duration_days' => 'nullable|integer|min:1|max:3650', // Max 10 years
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if tier name already exists for this sport
        $existingTier = Tier::where('sport_id', $request->sport_id)
            ->where('tier_name', $request->tier_name)
            ->first();

        if ($existingTier) {
            return response()->json([
                'status' => 'error',
                'message' => 'A tier with this name already exists for this sport'
            ], 422);
        }

        $tier = Tier::create($request->validated());
        $tier->load('sport:id,name,display_name');

        return response()->json([
            'status' => 'success',
            'message' => 'Tier created successfully',
            'data' => [
                'tier' => $tier
            ]
        ], 201);
    }

    /**
     * Display the specified tier.
     */
    public function show(Tier $tier): JsonResponse
    {
        $tier->load('sport:id,name,display_name');

        return response()->json([
            'status' => 'success',
            'data' => [
                'tier' => $tier
            ]
        ]);
    }

    /**
     * Update the specified tier.
     */
    public function update(Request $request, Tier $tier): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sport_id' => 'sometimes|required|exists:sports,id',
            'tier_name' => 'sometimes|required|string|max:50',
            'display_name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0|max:99999999.99',
            'duration_days' => 'nullable|integer|min:1|max:3650',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if tier name already exists for this sport (excluding current tier)
        if ($request->filled('tier_name') || $request->filled('sport_id')) {
            $sportId = $request->get('sport_id', $tier->sport_id);
            $tierName = $request->get('tier_name', $tier->tier_name);
            
            $existingTier = Tier::where('sport_id', $sportId)
                ->where('tier_name', $tierName)
                ->where('id', '!=', $tier->id)
                ->first();

            if ($existingTier) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'A tier with this name already exists for this sport'
                ], 422);
            }
        }

        $tier->update($request->validated());
        $tier->load('sport:id,name,display_name');

        return response()->json([
            'status' => 'success',
            'message' => 'Tier updated successfully',
            'data' => [
                'tier' => $tier
            ]
        ]);
    }

    /**
     * Remove the specified tier.
     */
    public function destroy(Tier $tier): JsonResponse
    {
        $tier->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Tier deleted successfully'
        ]);
    }

    /**
     * Toggle the active status of a tier.
     */
    public function toggleStatus(Tier $tier): JsonResponse
    {
        $tier->update([
            'is_active' => !$tier->is_active
        ]);

        $tier->load('sport:id,name,display_name');

        return response()->json([
            'status' => 'success',
            'message' => 'Tier status updated successfully',
            'data' => [
                'tier' => $tier
            ]
        ]);
    }

    /**
     * Get tiers for a specific sport.
     */
    public function getBySport(Request $request, Sport $sport): JsonResponse
    {
        $query = $sport->tiers();

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Filter by available (within date range and active)
        if ($request->boolean('available')) {
            $query->active()->available();
        }

        // Filter by price range
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('tier_name', 'ILIKE', "%{$search}%")
                  ->orWhere('display_name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'price');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $tiers = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'sport' => $sport->only(['id', 'name', 'display_name']),
                'tiers' => $tiers->items(),
                'pagination' => [
                    'current_page' => $tiers->currentPage(),
                    'last_page' => $tiers->lastPage(),
                    'per_page' => $tiers->perPage(),
                    'total' => $tiers->total(),
                ]
            ]
        ]);
    }

    /**
     * Get available tiers for a specific sport (public endpoint).
     */
    public function getAvailableBySport(Request $request, Sport $sport): JsonResponse
    {
        $query = $sport->tiers()->active()->available();

        // Filter by price range
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'price');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $tiers = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'sport' => $sport->only(['id', 'name', 'display_name']),
                'tiers' => $tiers
            ]
        ]);
    }
}
