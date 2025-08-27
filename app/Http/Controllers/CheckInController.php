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
use App\Http\Requests\StoreCheckInRequest;
use App\Http\Requests\UpdateCheckInRequest;

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

        // Filter by check-out status
        if ($request->has('checked_out')) {
            if ($request->checked_out) {
                $query->whereNotNull('check_out_time');
            } else {
                $query->whereNull('check_out_time');
            }
        }

        $checkIns = $query->orderBy('check_in_time', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $checkIns
        ]);
    }

    /**
     * Store a newly created check-in.
     */
    public function store(StoreCheckInRequest $request)
    {
        $user = User::find($request->user_id);
        $club = Club::find($request->club_id);

        // Check if user has active membership for sports offered by this club
        $membership = null;
        if ($request->membership_id) {
            // If specific membership_id provided, validate it
            $membership = Membership::where('id', $request->membership_id)
                ->where('user_id', $request->user_id)
                ->where('status', 'active')
                ->whereHas('sport', function($query) use ($request) {
                    $query->whereHas('clubs', function($q) use ($request) {
                        $q->where('clubs.id', $request->club_id);
                    });
                })
                ->first();
        } else {
            // Find any active membership for sports offered by this club
            $membership = Membership::where('user_id', $request->user_id)
                ->where('status', 'active')
                ->whereHas('sport', function($query) use ($request) {
                    $query->whereHas('clubs', function($q) use ($request) {
                        $q->where('clubs.id', $request->club_id);
                    });
                })
                ->first();
        }

        if (!$membership) {
            return response()->json([
                'status' => 'error',
                'message' => 'User does not have an active membership for any sport offered by this club'
            ], 422);
        }

        // Check if user is already checked in and hasn't checked out
        $existingCheckIn = CheckIn::where('user_id', $request->user_id)
            ->where('club_id', $request->club_id)
            ->whereNull('check_out_time')
            ->first();

        if ($existingCheckIn) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is already checked in to this club'
            ], 422);
        }

        // Check membership validity
        if ($membership->end_date && now()->isAfter($membership->end_date)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Membership has expired'
            ], 422);
        }

        $checkIn = CheckIn::create([
            'club_id' => $request->club_id,
            'user_id' => $request->user_id,
            'membership_id' => $membership->id,
            'check_in_time' => now(),
            'check_in_method' => $request->check_in_method ?? 'manual',
            'location' => $request->location,
            'notes' => $request->notes,
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
    public function update(UpdateCheckInRequest $request, CheckIn $checkIn)
    {
        $checkIn->update($request->validated());

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
     * Check out a user from a club.
     */
    public function checkOut(Request $request, CheckIn $checkIn)
    {
        if ($checkIn->check_out_time) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is already checked out'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $checkIn->update([
            'check_out_time' => now(),
            'location' => $request->location ?? $checkIn->location,
            'notes' => $request->notes ?? $checkIn->notes,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Check-out successful',
            'data' => $checkIn->load(['user', 'club', 'membership'])
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

        // Filter by checked out status
        if ($request->has('checked_out')) {
            if ($request->checked_out) {
                $query->whereNotNull('check_out_time');
            } else {
                $query->whereNull('check_out_time');
            }
        }

        $checkIns = $query->orderBy('check_in_time', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $checkIns
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
            'data' => $checkIns
        ]);
    }

    /**
     * Get current check-ins (users who haven't checked out).
     */
    public function currentCheckIns(Request $request)
    {
        $query = CheckIn::with(['user', 'club', 'membership'])
            ->whereNull('check_out_time');

        // Filter by club
        if ($request->has('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        $checkIns = $query->orderBy('check_in_time', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $checkIns
        ]);
    }

    /**
     * Quick check-in using QR code.
     */
    public function qrCheckIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
            'club_id' => 'required|uuid|exists:clubs,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Decode QR code to get user information
        // This is a simplified implementation - in reality you'd decode the QR
        $qrData = json_decode(base64_decode($request->qr_code), true);

        if (!$qrData || !isset($qrData['user_id'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid QR code'
            ], 422);
        }

        $user = User::find($qrData['user_id']);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Create check-in request data
        $checkInData = [
            'club_id' => $request->club_id,
            'user_id' => $user->id,
            'check_in_method' => 'qr_code',
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
            'average_session_duration' => (clone $query)->whereNotNull('check_out_time')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, check_in_time, check_out_time)) as avg_duration')
                ->first()->avg_duration ?? 0,
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
