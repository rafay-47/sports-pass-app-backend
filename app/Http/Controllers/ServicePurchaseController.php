<?php

namespace App\Http\Controllers;

use App\Models\ServicePurchase;
use App\Models\User;
use App\Models\Membership;
use App\Models\SportService;
use App\Http\Requests\StoreServicePurchaseRequest;
use App\Http\Requests\UpdateServicePurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServicePurchaseController extends Controller
{
    /**
     * Display a listing of service purchases.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ServicePurchase::with([
            'user:id,name,email',
            'membership:id,membership_number,sport_id,tier_id',
            'membership.sport:id,name,display_name',
            'membership.tier:id,tier_name,display_name',
            'sportService:id,service_name,base_price,type,rating'
        ]);

        // Role-based filtering
        if (!in_array($request->user()->user_role, ['admin', 'owner'])) {
            // Regular users can only see their own service purchases
            $query->where('user_id', $request->user()->id);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by membership
        if ($request->filled('membership_id')) {
            $query->where('membership_id', $request->membership_id);
        }

        // Filter by sport service
        if ($request->filled('sport_service_id')) {
            $query->where('sport_service_id', $request->sport_service_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            if (is_array($request->status)) {
                $query->whereIn('status', $request->status);
            } else {
                $query->where('status', $request->status);
            }
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $servicePurchases = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'service_purchases' => $servicePurchases->items(),
                'pagination' => [
                    'current_page' => $servicePurchases->currentPage(),
                    'last_page' => $servicePurchases->lastPage(),
                    'per_page' => $servicePurchases->perPage(),
                    'total' => $servicePurchases->total(),
                ]
            ]
        ]);
    }



    /**
     * Store a newly created service purchase.
     */
    public function store(StoreServicePurchaseRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $sportService = SportService::findOrFail($request->sport_service_id);
            
            $servicePurchaseData = [
                'user_id' => $request->user_id,
                'membership_id' => $request->membership_id,
                'sport_service_id' => $request->sport_service_id,
                'amount' => $request->amount ?? $sportService->discounted_price ?? $sportService->base_price,
                'status' => $request->status ?? 'completed',
                'service_date' => $request->service_date ? Carbon::parse($request->service_date) : now()->toDateString(),
                'service_time' => $request->service_time,
                'provider' => $request->provider,
                'location' => $request->location,
                'notes' => $request->notes,
            ];

            $servicePurchase = ServicePurchase::create($servicePurchaseData);
            $servicePurchase->load([
                'user:id,name,email',
                'membership:id,membership_number,sport_id,tier_id',
                'membership.sport:id,name,display_name',
                'membership.tier:id,tier_name,display_name',
                'sportService:id,service_name,base_price,type,rating'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Service purchase created successfully',
                'data' => [
                    'service_purchase' => $servicePurchase
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create service purchase',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified service purchase.
     */
    public function show(Request $request, ServicePurchase $servicePurchase): JsonResponse
    {
        // Authorization check
        if (!in_array($request->user()->user_role, ['admin', 'owner']) && 
            $servicePurchase->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to view this service purchase'
            ], 403);
        }

        $servicePurchase->load([
            'user:id,name,email',
            'membership:id,membership_number,sport_id,tier_id',
            'membership.sport:id,name,display_name,icon,color',
            'membership.tier:id,tier_name,display_name,price',
            'sportService:id,service_name,description,base_price,type,rating,duration_minutes'
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'service_purchase' => $servicePurchase
            ]
        ]);
    }



    /**
     * Update the specified service purchase.
     */
    public function update(UpdateServicePurchaseRequest $request, ServicePurchase $servicePurchase): JsonResponse
    {
        try {
            $servicePurchase->update($request->validated());
            $servicePurchase->load([
                'user:id,name,email',
                'membership:id,membership_number,sport_id,tier_id',
                'membership.sport:id,name,display_name',
                'membership.tier:id,tier_name,display_name',
                'sportService:id,service_name,base_price,type,rating'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Service purchase updated successfully',
                'data' => [
                    'service_purchase' => $servicePurchase
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update service purchase',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified service purchase.
     */
    public function destroy(Request $request, ServicePurchase $servicePurchase): JsonResponse
    {
        // Only admins and owners can delete service purchases
        if (!in_array($request->user()->user_role, ['admin', 'owner'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to delete service purchases'
            ], 403);
        }

        try {
            $servicePurchase->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Service purchase deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete service purchase',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark service purchase as completed.
     */
    public function markCompleted(Request $request, ServicePurchase $servicePurchase): JsonResponse
    {
        // Authorization check
        if (!in_array($request->user()->user_role, ['admin', 'owner']) && 
            $servicePurchase->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to modify this service purchase'
            ], 403);
        }

        if ($servicePurchase->markCompleted()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Service purchase marked as completed',
                'data' => [
                    'service_purchase' => $servicePurchase
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to mark service purchase as completed'
        ], 400);
    }

    /**
     * Cancel service purchase.
     */
    public function cancel(Request $request, ServicePurchase $servicePurchase): JsonResponse
    {
        // Authorization check
        if (!in_array($request->user()->user_role, ['admin', 'owner']) && 
            $servicePurchase->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to cancel this service purchase'
            ], 403);
        }

        if ($servicePurchase->cancel()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Service purchase cancelled successfully',
                'data' => [
                    'service_purchase' => $servicePurchase
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to cancel service purchase'
        ], 400);
    }

    /**
     * Get user's service purchases.
     */
    public function myPurchases(Request $request): JsonResponse
    {
        $servicePurchases = ServicePurchase::with([
            'membership:id,membership_number,sport_id,tier_id',
            'membership.sport:id,name,display_name,icon,color',
            'membership.tier:id,tier_name,display_name',
            'sportService:id,service_name,description,base_price,type,rating,duration_minutes'
        ])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => [
                'service_purchases' => $servicePurchases->items(),
                'pagination' => [
                    'current_page' => $servicePurchases->currentPage(),
                    'last_page' => $servicePurchases->lastPage(),
                    'per_page' => $servicePurchases->perPage(),
                    'total' => $servicePurchases->total(),
                ]
            ]
        ]);
    }
}
