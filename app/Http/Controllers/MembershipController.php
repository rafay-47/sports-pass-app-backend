<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\User;
use App\Models\Sport;
use App\Models\Tier;
use App\Http\Requests\StoreMembershipRequest;
use App\Http\Requests\UpdateMembershipRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MembershipController extends Controller
{
    /**
     * Display a listing of memberships.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Membership::with(['user:id,name,email', 'sport:id,name,display_name', 'tier:id,tier_name,display_name,price']);

        // Role-based filtering
        if (!in_array($request->user()->user_role, ['admin', 'owner'])) {
            // Regular users can only see their own memberships
            $query->where('user_id', $request->user()->id);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by sport
        if ($request->filled('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by active memberships
        if ($request->boolean('active')) {
            $query->active()->where('expiry_date', '>=', now());
        }

        // Filter by expired memberships
        if ($request->boolean('expired')) {
            $query->expired();
        }

        // Filter by expiring soon
        if ($request->boolean('expiring_soon')) {
            $days = $request->get('expiring_days', 30);
            $query->expiringSoon($days);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('membership_number', 'ILIKE', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'ILIKE', "%{$search}%")
                               ->orWhere('email', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('sport', function ($sportQuery) use ($search) {
                      $sportQuery->where('name', 'ILIKE', "%{$search}%")
                                ->orWhere('display_name', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Handle sorting by related fields
        if ($sortBy === 'user_name') {
            $query->join('users', 'memberships.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortOrder)
                  ->select('memberships.*');
        } elseif ($sortBy === 'sport_name') {
            $query->join('sports', 'memberships.sport_id', '=', 'sports.id')
                  ->orderBy('sports.name', $sortOrder)
                  ->select('memberships.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $memberships = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'memberships' => $memberships->items(),
                'pagination' => [
                    'current_page' => $memberships->currentPage(),
                    'last_page' => $memberships->lastPage(),
                    'per_page' => $memberships->perPage(),
                    'total' => $memberships->total(),
                ]
            ]
        ]);
    }

    /**
     * Store a newly created membership.
     */
    public function store(StoreMembershipRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $tier = Tier::findOrFail($request->tier_id);
            
            $membershipData = [
                'user_id' => $request->user_id,
                'sport_id' => $request->sport_id,
                'tier_id' => $request->tier_id,
                'status' => 'active',
                'purchase_date' => now(),
                'start_date' => $request->start_date ? Carbon::parse($request->start_date) : now(),
                'auto_renew' => $request->boolean('auto_renew', true),
                'purchase_amount' => $request->purchase_amount ?? $tier->price,
                'monthly_check_ins' => 0,
                'total_spent' => 0,
                'monthly_spent' => 0,
                'total_earnings' => 0,
                'monthly_earnings' => 0,
            ];

            // Calculate expiry date
            $startDate = Carbon::parse($membershipData['start_date']);
            $membershipData['expiry_date'] = $startDate->addDays($tier->duration_days);

            $membership = Membership::create($membershipData);
            $membership->load(['user:id,name,email', 'sport:id,name,display_name', 'tier:id,tier_name,display_name,price']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Membership created successfully',
                'data' => [
                    'membership' => $membership
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create membership',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified membership.
     */
    public function show(Request $request, Membership $membership): JsonResponse
    {
        // Authorization check
        if (!in_array($request->user()->user_role, ['admin', 'owner']) && 
            $membership->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to view this membership'
            ], 403);
        }

        $membership->load(['user:id,name,email', 'sport:id,name,display_name', 'tier:id,tier_name,display_name,price,features']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'membership' => $membership
            ]
        ]);
    }

    /**
     * Update the specified membership.
     */
    public function update(UpdateMembershipRequest $request, Membership $membership): JsonResponse
    {
        try {
            $membership->update($request->validated());
            $membership->load(['user:id,name,email', 'sport:id,name,display_name', 'tier:id,tier_name,display_name,price']);

            return response()->json([
                'status' => 'success',
                'message' => 'Membership updated successfully',
                'data' => [
                    'membership' => $membership
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update membership',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified membership.
     */
    public function destroy(Request $request, Membership $membership): JsonResponse
    {
        // Only admins and owners can delete memberships
        if (!in_array($request->user()->user_role, ['admin', 'owner'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to delete memberships'
            ], 403);
        }

        try {
            $membership->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Membership deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete membership',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Renew a membership.
     */
    public function renew(Request $request, Membership $membership): JsonResponse
    {
        // Authorization check
        if (!in_array($request->user()->user_role, ['admin', 'owner']) && 
            $membership->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to renew this membership'
            ], 403);
        }

        if ($membership->renew()) {
            $membership->load(['user:id,name,email', 'sport:id,name,display_name', 'tier:id,tier_name,display_name,price']);

            return response()->json([
                'status' => 'success',
                'message' => 'Membership renewed successfully',
                'data' => [
                    'membership' => $membership
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to renew membership'
        ], 400);
    }

    /**
     * Pause a membership.
     */
    public function pause(Request $request, Membership $membership): JsonResponse
    {
        // Authorization check
        if (!in_array($request->user()->user_role, ['admin', 'owner']) && 
            $membership->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to pause this membership'
            ], 403);
        }

        if ($membership->pause()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Membership paused successfully',
                'data' => [
                    'membership' => $membership
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to pause membership'
        ], 400);
    }

    /**
     * Resume a membership.
     */
    public function resume(Request $request, Membership $membership): JsonResponse
    {
        // Authorization check
        if (!in_array($request->user()->user_role, ['admin', 'owner']) && 
            $membership->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to resume this membership'
            ], 403);
        }

        if ($membership->resume()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Membership resumed successfully',
                'data' => [
                    'membership' => $membership
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to resume membership'
        ], 400);
    }

    /**
     * Cancel a membership.
     */
    public function cancel(Request $request, Membership $membership): JsonResponse
    {
        // Authorization check
        if (!in_array($request->user()->user_role, ['admin', 'owner']) && 
            $membership->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to cancel this membership'
            ], 403);
        }

        if ($membership->cancel()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Membership cancelled successfully',
                'data' => [
                    'membership' => $membership
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to cancel membership'
        ], 400);
    }

    /**
     * Get membership statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = Membership::query();

        // Role-based filtering
        if (!in_array($request->user()->user_role, ['admin', 'owner'])) {
            $query->where('user_id', $request->user()->id);
        }

        $stats = [
            'total_memberships' => $query->count(),
            'active_memberships' => $query->active()->where('expiry_date', '>=', now())->count(),
            'expired_memberships' => $query->expired()->count(),
            'paused_memberships' => $query->where('status', 'paused')->count(),
            'cancelled_memberships' => $query->where('status', 'cancelled')->count(),
            'expiring_soon' => $query->expiringSoon(30)->count(),
            'total_revenue' => $query->sum('purchase_amount'),
            'monthly_revenue' => $query->whereMonth('purchase_date', now())->sum('purchase_amount'),
        ];

        // Sports breakdown (admin/owner only)
        if (in_array($request->user()->user_role, ['admin', 'owner'])) {
            $stats['sports_breakdown'] = Membership::join('sports', 'memberships.sport_id', '=', 'sports.id')
                ->selectRaw('sports.name, sports.display_name, COUNT(*) as count, SUM(purchase_amount) as revenue')
                ->groupBy('sports.id', 'sports.name', 'sports.display_name')
                ->get();

            $stats['tier_breakdown'] = Membership::join('tiers', 'memberships.tier_id', '=', 'tiers.id')
                ->selectRaw('tiers.tier_name, tiers.display_name, COUNT(*) as count, SUM(purchase_amount) as revenue')
                ->groupBy('tiers.id', 'tiers.tier_name', 'tiers.display_name')
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'statistics' => $stats
            ]
        ]);
    }

    /**
     * Get user's active memberships.
     */
    public function myMemberships(Request $request): JsonResponse
    {
        $memberships = Membership::with(['sport:id,name,display_name,icon,color', 'tier:id,tier_name,display_name,price,features'])
            ->where('user_id', $request->user()->id)
            ->active()
            ->where('expiry_date', '>=', now())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'memberships' => $memberships
            ]
        ]);
    }
}
