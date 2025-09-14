<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\Club;
use App\Models\User;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CheckInController extends Controller
{
    /**
     * Display a listing of check-ins.
     */
    public function index(Request $request)
    {
        $query = CheckIn::with(['user', 'club', 'membership']);

        // Filter by club
        if ($request->has('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by membership
        if ($request->has('membership_id')) {
            $query->where('membership_id', $request->membership_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('check_in_time', [$request->start_date, $request->end_date]);
        }

        // Filter by today
        if ($request->has('today') && $request->today) {
            $query->whereDate('check_in_time', today());
        }

        $checkIns = $query->orderBy('check_in_time', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'check_ins' => $checkIns
            ]
        ]);
    }

    /**
     * Store a newly created check-in.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'club_id' => 'required|uuid|exists:clubs,id',
            'user_id' => 'required|uuid|exists:users,id',
            'membership_id' => 'nullable|uuid|exists:memberships,id',
            'notes' => 'nullable|string|max:500',
            'qr_code_used' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($request->user_id);
        $club = Club::find($request->club_id);

        // Check if user has active membership for the club's sport
        $membership = null;
        if ($request->membership_id) {
            // If specific membership_id provided, validate it
            $membership = Membership::where('id', $request->membership_id)
                ->where('user_id', $request->user_id)
                ->where('status', 'active')
                ->where('expiry_date', '>=', now())
                ->where('sport_id', $club->sport_id)
                ->first();
        } else {
            // Find any active membership for the club's sport
            $membership = Membership::where('user_id', $request->user_id)
                ->where('status', 'active')
                ->where('expiry_date', '>=', now())
                ->where('sport_id', $club->sport_id)
                ->first();
        }

        if (!$membership) {
            return response()->json([
                'status' => 'error',
                'message' => 'User does not have an active membership for any sport offered by this club'
            ], 422);
        }

        // Check membership validity
        if ($membership->expiry_date && now()->isAfter($membership->expiry_date)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Membership has expired'
            ], 422);
        }

        // Check if user already checked in today with this membership
        $existingCheckIn = CheckIn::where('user_id', $request->user_id)
            ->where('membership_id', $membership->id)
            ->where('check_in_date', today())
            ->first();

        if ($existingCheckIn) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already checked in today with this membership'
            ], 422);
        }

        $checkIn = CheckIn::create([
            'club_id' => $request->club_id,
            'user_id' => $request->user_id,
            'membership_id' => $membership->id,
            'check_in_date' => today(),
            'check_in_time' => now(),
            'notes' => $request->notes,
            'qr_code_used' => $request->qr_code_used,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Check-in successful',
            'data' => $checkIn->load(['user', 'club', 'membership'])
        ], 201);
    }

    /**
     * Display the specified check-in.
     */
    public function show(CheckIn $checkIn)
    {
        $checkIn->load(['user', 'club', 'membership']);

        return response()->json([
            'status' => 'success',
            'data' => $checkIn
        ]);
    }

    /**
     * Update the specified check-in.
     */
    public function update(Request $request, CheckIn $checkIn)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $checkIn->update($request->only(['notes']));

        return response()->json([
            'status' => 'success',
            'message' => 'Check-in updated successfully',
            'data' => $checkIn->load(['user', 'club', 'membership'])
        ]);
    }

    /**
     * Remove the specified check-in.
     */
    public function destroy(CheckIn $checkIn)
    {
        $checkIn->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Check-in deleted successfully'
        ]);
    }

    /**
     * Get check-ins for a specific club.
     */
    public function getByClub(Club $club, Request $request)
    {
        $query = $club->checkIns()->with(['user', 'membership']);

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('check_in_time', $request->date);
        }

        $checkIns = $query->orderBy('check_in_time', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => [
                'check_ins' => $checkIns->items(),
                'pagination' => [
                    'current_page' => $checkIns->currentPage(),
                    'last_page' => $checkIns->lastPage(),
                    'per_page' => $checkIns->perPage(),
                    'total' => $checkIns->total(),
                ]
            ]
        ]);
    }

    /**
     * Get check-ins for a specific user.
     */
    public function getByUser(User $user, Request $request)
    {
        $query = $user->checkIns()->with(['club', 'membership']);

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('check_in_time', [$request->start_date, $request->end_date]);
        }

        $checkIns = $query->orderBy('check_in_time', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => [
                'check_ins' => $checkIns->items(),
                'pagination' => [
                    'current_page' => $checkIns->currentPage(),
                    'last_page' => $checkIns->lastPage(),
                    'per_page' => $checkIns->perPage(),
                    'total' => $checkIns->total(),
                ]
            ]
        ]);
    }

    /**
     * Get all check-ins.
     */
    public function currentCheckIns(Request $request)
    {
        $query = CheckIn::with(['user', 'club', 'membership']);

        // Filter by club
        if ($request->has('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        $checkIns = $query->orderBy('check_in_time', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => [
                'check_ins' => $checkIns->items(),
                'pagination' => [
                    'current_page' => $checkIns->currentPage(),
                    'last_page' => $checkIns->lastPage(),
                    'per_page' => $checkIns->perPage(),
                    'total' => $checkIns->total(),
                ]
            ]
        ]);
    }

    /**
     * Quick check-in using QR code.
     */
    public function qrCheckIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get authenticated user
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication required'
            ], 401);
        }
        
        // Find club by QR code
        $club = Club::findByQrCode($request->qr_code);

        if (!$club) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid QR code or club not found'
            ], 404);
        }


        // Check if club is active
        if (!$club->is_active || $club->status !== 'active') {
            return response()->json([
                'status' => 'error',
                'message' => 'Club is not available for check-in'
            ], 422);
        }

        // Create check-in request data
        $checkInData = [
            'club_id' => $club->id,
            'user_id' => $user->id,
            'qr_code_used' => $request->qr_code,
        ];

        // Use the store method to create the check-in
        $checkInRequest = new Request($checkInData);
        return $this->store($checkInRequest);
    }

    /**
     * Get check-in statistics.
     */
    public function statistics(Request $request)
    {
        $query = CheckIn::query();

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('check_in_time', [$request->start_date, $request->end_date]);
        } else {
            // Default to current month
            $query->whereBetween('check_in_time', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ]);
        }

        $stats = [
            'total_check_ins' => (clone $query)->count(),
            'unique_users' => (clone $query)->distinct('user_id')->count('user_id'),
            'check_ins_by_club' => (clone $query)->selectRaw('club_id, COUNT(*) as count')
                ->groupBy('club_id')
                ->with('club')
                ->get(),
            'check_ins_by_day' => (clone $query)->selectRaw('DATE(check_in_time) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date'),
            'check_ins_by_hour' => (clone $query)->selectRaw('HOUR(check_in_time) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->orderBy('hour')
                ->pluck('count', 'hour'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}
