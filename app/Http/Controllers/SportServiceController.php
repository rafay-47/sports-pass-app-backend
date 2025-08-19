<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Models\SportService;
use App\Http\Requests\StoreSportServiceRequest;
use App\Http\Requests\UpdateSportServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SportServiceController extends Controller
{
    /**
     * Display a listing of sport services.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SportService::with('sport:id,name,display_name');

        // Filter by sport
        if ($request->filled('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Filter by service type
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Filter by popular services
        if ($request->boolean('popular_only')) {
            $query->popular();
        }

        // Filter by minimum rating
        if ($request->filled('min_rating')) {
            $query->byMinRating($request->min_rating);
        }

        // Filter trainer services only
        if ($request->boolean('trainers_only')) {
            $query->trainers();
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('service_name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'service_name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Handle special sorting cases
        if ($sortBy === 'rating') {
            $query->orderByRating($sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $services = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'services' => $services->items(),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                ]
            ]
        ]);
    }

    /**
     * Store a newly created sport service.
     */
    public function store(StoreSportServiceRequest $request): JsonResponse
    {
        // Check if service name already exists for this sport
        $existingService = SportService::where('sport_id', $request->sport_id)
            ->where('service_name', $request->service_name)
            ->first();

        if ($existingService) {
            return response()->json([
                'status' => 'error',
                'message' => 'A service with this name already exists for this sport'
            ], 422);
        }

        $service = SportService::create($request->validated());
        $service->load('sport:id,name,display_name');

        return response()->json([
            'status' => 'success',
            'message' => 'Sport service created successfully',
            'data' => [
                'service' => $service
            ]
        ], 201);
    }

    /**
     * Display the specified sport service.
     */
    public function show(SportService $sportService): JsonResponse
    {
        $sportService->load('sport:id,name,display_name');

        return response()->json([
            'status' => 'success',
            'data' => [
                'service' => $sportService
            ]
        ]);
    }

    /**
     * Update the specified sport service.
     */
    public function update(UpdateSportServiceRequest $request, SportService $sportService): JsonResponse
    {
        // Check if service name already exists for this sport (excluding current service)
        if ($request->filled('service_name') || $request->filled('sport_id')) {
            $sportId = $request->get('sport_id', $sportService->sport_id);
            $serviceName = $request->get('service_name', $sportService->service_name);
            
            $existingService = SportService::where('sport_id', $sportId)
                ->where('service_name', $serviceName)
                ->where('id', '!=', $sportService->id)
                ->first();

            if ($existingService) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'A service with this name already exists for this sport'
                ], 422);
            }
        }

        $sportService->update($request->validated());
        $sportService->load('sport:id,name,display_name');

        return response()->json([
            'status' => 'success',
            'message' => 'Sport service updated successfully',
            'data' => [
                'service' => $sportService
            ]
        ]);
    }

    /**
     * Remove the specified sport service.
     */
    public function destroy(SportService $sportService): JsonResponse
    {
        $sportService->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sport service deleted successfully'
        ]);
    }

    /**
     * Toggle the active status of a sport service.
     */
    public function toggleStatus(SportService $sportService): JsonResponse
    {
        $sportService->update([
            'is_active' => !$sportService->is_active
        ]);

        $sportService->load('sport:id,name,display_name');

        return response()->json([
            'status' => 'success',
            'message' => 'Sport service status updated successfully',
            'data' => [
                'service' => $sportService
            ]
        ]);
    }

    /**
     * Get services for a specific sport.
     */
    public function getBySport(Request $request, Sport $sport): JsonResponse
    {
        $query = $sport->services();

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('service_name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'service_name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $services = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'sport' => $sport->only(['id', 'name', 'display_name']),
                'services' => $services->items(),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                ]
            ]
        ]);
    }

    /**
     * Get service prices for a specific sport.
     */
    public function getServicePricesBySport(Request $request, Sport $sport): JsonResponse
    {
        $query = $sport->services();

        // Filter by active status (default to active only)
        $query->where('is_active', $request->boolean('include_inactive', false) ? null : true);

        // Get only necessary fields for pricing
        $services = $query->select([
            'id',
            'service_name',
            'base_price',
            'discount_percentage',
            'duration_minutes',
            'is_active'
        ])->get();

        // Transform the data to include calculated discounted price
        $servicesPrices = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'service_name' => $service->service_name,
                'base_price' => $service->base_price,
                'discounted_price' => $service->discounted_price,
                'discount_percentage' => $service->discount_percentage,
                'duration_minutes' => $service->duration_minutes,
                'savings' => $service->discount_percentage > 0 
                    ? round($service->base_price - $service->discounted_price, 2) 
                    : 0,
                'is_active' => $service->is_active
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'sport' => $sport->only(['id', 'name', 'display_name']),
                'services_count' => $servicesPrices->count(),
                'total_base_price' => $servicesPrices->sum('base_price'),
                'total_discounted_price' => $servicesPrices->sum('discounted_price'),
                'total_savings' => $servicesPrices->sum('savings'),
                'services' => $servicesPrices
            ]
        ]);
    }

    /**
     * Get popular services across all sports.
     */
    public function getPopularServices(Request $request): JsonResponse
    {
        $query = SportService::with('sport:id,name,display_name')
            ->popular()
            ->active();

        // Filter by service type
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Filter by minimum rating
        if ($request->filled('min_rating')) {
            $query->byMinRating($request->min_rating);
        }

        $services = $query->orderByRating('desc')
            ->limit($request->get('limit', 10))
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'services' => $services
            ]
        ]);
    }

    /**
     * Get services by type.
     */
    public function getServicesByType(Request $request, string $type): JsonResponse
    {
        $query = SportService::with('sport:id,name,display_name')
            ->byType($type)
            ->active();

        // Filter by sport
        if ($request->filled('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        // Filter by minimum rating
        if ($request->filled('min_rating')) {
            $query->byMinRating($request->min_rating);
        }

        // Filter by popular services
        if ($request->boolean('popular_only')) {
            $query->popular();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'rating');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'rating') {
            $query->orderByRating($sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $services = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'type' => $type,
                'services' => $services->items(),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                ]
            ]
        ]);
    }

    /**
     * Toggle popular status of a service.
     */
    public function togglePopular(SportService $sportService): JsonResponse
    {
        $sportService->update([
            'is_popular' => !$sportService->is_popular
        ]);

        $sportService->load('sport:id,name,display_name');

        return response()->json([
            'status' => 'success',
            'message' => 'Service popular status updated successfully',
            'data' => [
                'service' => $sportService
            ]
        ]);
    }

    /**
     * Get service statistics.
     */
    public function getStatistics(): JsonResponse
    {
        $stats = [
            'total_services' => SportService::count(),
            'active_services' => SportService::active()->count(),
            'popular_services' => SportService::popular()->count(),
            'average_rating' => round(SportService::where('rating', '>', 0)->avg('rating'), 2),
            'type_breakdown' => SportService::selectRaw('type, COUNT(*) as count, AVG(rating) as avg_rating')
                ->groupBy('type')
                ->orderBy('count', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'type' => $item->type,
                        'count' => $item->count,
                        'average_rating' => round($item->avg_rating, 2)
                    ];
                }),
            'rating_distribution' => [
                'excellent' => SportService::where('rating', '>=', 4.5)->count(),
                'very_good' => SportService::whereBetween('rating', [4.0, 4.49])->count(),
                'good' => SportService::whereBetween('rating', [3.5, 3.99])->count(),
                'average' => SportService::whereBetween('rating', [3.0, 3.49])->count(),
                'below_average' => SportService::where('rating', '<', 3.0)->count(),
            ]
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'statistics' => $stats
            ]
        ]);
    }
}
