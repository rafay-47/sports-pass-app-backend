<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SportController extends Controller
{
    /**
     * Display a listing of sports.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Sport::query();

        // Always include tiers and services for pricing calculations
        $withRelations = ['activeTiers', 'activeServices'];
        
        $query->with($withRelations);

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('display_name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $sports = $query->orderBy('name')->paginate($perPage);

        // Add pricing information to each sport
        $sportsWithPricing = $sports->getCollection()->map(function ($sport) {
            $sportArray = $sport->toArray();
            $sportArray['pricing'] = $sport->getServicesPricingInfo();
            return $sportArray;
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'sports' => $sportsWithPricing,
                'pagination' => [
                    'current_page' => $sports->currentPage(),
                    'last_page' => $sports->lastPage(),
                    'per_page' => $sports->perPage(),
                    'total' => $sports->total(),
                ]
            ]
        ]);
    }

    /**
     * Store a newly created sport.
     */
    public function store(Request $request): JsonResponse
    {
        // Additional authorization check (redundant with middleware, but good practice)
        if ($request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only admins can create sports'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:sports,name',
            'display_name' => 'required|string|max:100',
            'icon' => 'required|string',
            'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string',
            'number_of_services' => 'integer|min:0',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sportData = $request->only([
                'name', 'display_name', 'icon', 'color', 'description', 'number_of_services', 'is_active'
            ]);

            // Handle icon file upload
            if ($request->hasFile('icon_file')) {
                $uploadResponse = app(\App\Http\Controllers\UploadController::class)->upload(new Request([
                    'file' => $request->file('icon_file'),
                    'type' => 'sport_icon',
                    'related_id' => '', // Will update after creation
                    'file_type' => 'image',
                ]));

                if ($uploadResponse->getStatusCode() === 200) {
                    $uploadData = json_decode($uploadResponse->getContent(), true);
                    $sportData['icon'] = $uploadData['data']['url'];
                }
            }

            $sport = Sport::create($sportData);

            // Update the upload with the actual sport ID
            if ($request->hasFile('icon_file')) {
                app(\App\Http\Controllers\UploadController::class)->upload(new Request([
                    'file' => $request->file('icon_file'),
                    'type' => 'sport_icon',
                    'related_id' => $sport->id,
                    'file_type' => 'image',
                ]));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Sport created successfully',
                'data' => [
                    'sport' => $sport
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create sport',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified sport.
     */
    public function show(Sport $sport): JsonResponse
    {
        // Always load tiers, only load services if explicitly needed
        $sport->load(['activeTiers']);
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'sport' => $sport
            ]
        ]);
    }

    /**
     * Update the specified sport.
     */
    public function update(Request $request, Sport $sport): JsonResponse
    {
        // Additional authorization check
        if ($request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only admins can update sports'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100|unique:sports,name,' . $sport->id,
            'display_name' => 'sometimes|string|max:100',
            'icon' => 'sometimes|string',
            'color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string',
            'number_of_services' => 'sometimes|integer|min:0',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sport->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Sport updated successfully',
                'data' => [
                    'sport' => $sport->fresh()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update sport',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified sport.
     */
    public function destroy(Request $request, Sport $sport): JsonResponse
    {
        // Additional authorization check
        if ($request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only admins can delete sports'
            ], 403);
        }

        try {
            $sport->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Sport deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete sport',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get only active sports.
     */
    public function active(Request $request): JsonResponse
    {
        try {
            $query = Sport::where('is_active', true);
            
            // Always include tiers and services for pricing calculations
            $withRelations = ['activeTiers', 'activeServices'];
            
            $query->with($withRelations);
            
            $sports = $query->orderBy('name')->get();

            // Add pricing information to each sport
            $sportsWithPricing = $sports->map(function ($sport) {
                $sportArray = $sport->toArray();
                $sportArray['pricing'] = $sport->getServicesPricingInfo();
                return $sportArray;
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'sports' => $sportsWithPricing
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve active sports',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sports with their available tiers (filtered by current date).
     */
    public function withAvailableTiers(Request $request): JsonResponse
    {
        try {
            $currentDate = now()->toDateString();
            
            $query = Sport::with([
                'activeTiers' => function ($query) use ($currentDate) {
                    $query->where(function ($q) use ($currentDate) {
                        $q->where(function ($subQ) use ($currentDate) {
                            $subQ->whereNull('start_date')
                                 ->orWhere('start_date', '<=', $currentDate);
                        })->where(function ($subQ) use ($currentDate) {
                            $subQ->whereNull('end_date')
                                 ->orWhere('end_date', '>=', $currentDate);
                        });
                    });
                },
                'activeServices' // Include services for pricing calculations
            ]);

            // Filter by active status
            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            } else {
                $query->where('is_active', true); // Default to active sports
            }

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('display_name', 'ILIKE', "%{$search}%")
                      ->orWhere('description', 'ILIKE', "%{$search}%");
                });
            }

            $sports = $query->orderBy('name')->get();

            // Add pricing information to each sport
            $sportsWithPricing = $sports->map(function ($sport) {
                $sportArray = $sport->toArray();
                $sportArray['pricing'] = $sport->getServicesPricingInfo();
                return $sportArray;
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'sports' => $sportsWithPricing,
                    'filtered_date' => $currentDate
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve sports with available tiers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle sport active status.
     */
    public function toggleStatus(Request $request, Sport $sport): JsonResponse
    {
        // Additional authorization check
        if ($request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only admins can toggle sport status'
            ], 403);
        }

        try {
            $sport->update(['is_active' => !$sport->is_active]);

            return response()->json([
                'status' => 'success',
                'message' => 'Sport status updated successfully',
                'data' => [
                    'sport' => $sport->fresh()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update sport status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
