<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainerRequestRequest;
use App\Http\Requests\UpdateTrainerRequestRequest;
use App\Http\Resources\TrainerRequestResource;
use App\Models\TrainerRequest;
use App\Models\TrainerProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrainerRequestController extends Controller
{
    /**
     * Display a listing of the user's trainer requests.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = TrainerRequest::where('user_id', $user->id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->with(['trainerProfile.user', 'acceptedByTrainer.user', 'service', 'club'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return TrainerRequestResource::collection($requests);
    }

    /**
     * Display a listing of incoming trainer requests for the authenticated trainer.
     */
    public function incoming(Request $request)
    {
        $user = Auth::user();
        $trainerProfile = TrainerProfile::where('user_id', $user->id)->first();

        if (!$trainerProfile) {
            return response()->json(['message' => 'Trainer profile not found'], 404);
        }

        $query = TrainerRequest::where('status', 'pending')
            ->where(function ($q) use ($trainerProfile) {
                $q->where('request_type', 'open_request')
                  ->orWhere(function ($subQ) use ($trainerProfile) {
                      $subQ->where('request_type', 'specific_trainer')
                           ->where('trainer_profile_id', $trainerProfile->id);
                  });
            });

        if ($request->has('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        $requests = $query->with(['user', 'membership', 'service', 'club'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return TrainerRequestResource::collection($requests);
    }

    /**
     * Store a newly created trainer request.
     */
    public function store(StoreTrainerRequestRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();

        // Ensure the membership belongs to the user
        $membership = $user->memberships()->find($validated['membership_id']);
        if (!$membership) {
            return response()->json(['message' => 'Membership not found'], 404);
        }

        // Validate trainer if specific request
        if ($validated['request_type'] === 'specific_trainer') {
            $trainer = TrainerProfile::find($validated['trainer_profile_id']);
            if (!$trainer || $trainer->sport_id !== $membership->sport_id) {
                return response()->json(['message' => 'Trainer sport does not match membership sport'], 400);
            } else if($trainer->tier_id !== $membership->tier_id) {
                return response()->json(['message' => 'Trainer tier does not match membership tier'], 400);
            }
            
        } 

        $trainerRequest = TrainerRequest::create([
            'user_id' => $user->id,
            'membership_id' => $validated['membership_id'],
            'sport_id' => $membership->sport_id,
            'tier_id' => $membership->tier_id,
            'service_id' => $validated['service_id'],
            'request_type' => $validated['request_type'],
            'trainer_profile_id' => $validated['trainer_profile_id'] ?? null,
            'club_id' => $validated['club_id'] ?? null,
            'preferred_time_slots' => $validated['preferred_time_slots'],
            'message' => $validated['message'] ?? null,
            'expires_at' => now()->addDays(7), // Default 7 days
        ]);

        return new TrainerRequestResource($trainerRequest->load(['trainerProfile.user', 'service', 'club']));
    }

    /**
     * Display the specified trainer request.
     */
    public function show(TrainerRequest $trainerRequest)
    {
        // Check if user owns the request or is the trainer
        $user = Auth::user();
        if ($trainerRequest->user_id !== $user->id) {
            $trainerProfile = TrainerProfile::where('user_id', $user->id)->first();
            if (!$trainerProfile || ($trainerRequest->request_type === 'specific_trainer' && $trainerRequest->trainer_profile_id !== $trainerProfile->id)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return new TrainerRequestResource($trainerRequest->load(['user', 'membership', 'trainerProfile.user', 'acceptedByTrainer.user', 'service', 'club']));
    }

    /**
     * Accept a trainer request.
     */
    public function accept(TrainerRequest $trainerRequest)
    {
        $user = Auth::user();
        $trainerProfile = TrainerProfile::where('user_id', $user->id)->first();

        if (!$trainerProfile) {
            return response()->json(['message' => 'Trainer profile not found'], 404);
        }

        // Check if the trainer can accept this request
        if ($trainerRequest->request_type === 'specific_trainer' && $trainerRequest->trainer_profile_id !== $trainerProfile->id) {
            return response()->json(['message' => 'You cannot accept this request'], 403);
        }

        // For open requests, validate that trainer matches the required sport and tier
        if ($trainerRequest->request_type === 'open_request') {
            if ($trainerProfile->sport_id !== $trainerRequest->sport_id || $trainerProfile->tier_id !== $trainerRequest->tier_id) {
                return response()->json(['message' => 'You are not qualified to accept this request'], 403);
            }
        }

        // Use database transaction with lock to prevent race conditions
        try {
            DB::transaction(function () use ($trainerRequest, $trainerProfile) {
                // Lock the record and check status again
                $lockedRequest = TrainerRequest::lockForUpdate()->find($trainerRequest->id);

                if ($lockedRequest->status !== 'pending') {
                    throw new \Exception('Request is no longer available');
                }

                $lockedRequest->update([
                    'status' => 'accepted',
                    'accepted_by_trainer_id' => $trainerProfile->id,
                    'accepted_at' => now(),
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return new TrainerRequestResource($trainerRequest->load(['user', 'membership', 'trainerProfile.user', 'acceptedByTrainer.user', 'service', 'club']));
    }

    /**
     * Decline a trainer request.
     */
    public function decline(TrainerRequest $trainerRequest, Request $request)
    {
        $user = Auth::user();
        $trainerProfile = TrainerProfile::where('user_id', $user->id)->first();

        if (!$trainerProfile) {
            return response()->json(['message' => 'Trainer profile not found'], 404);
        }

        // Check if the trainer can decline this request
        if ($trainerRequest->request_type === 'specific_trainer' && $trainerRequest->trainer_profile_id !== $trainerProfile->id) {
            return response()->json(['message' => 'You cannot decline this request'], 403);
        }

        if ($trainerRequest->status !== 'pending') {
            return response()->json(['message' => 'Request is not pending'], 400);
        }

        $trainerRequest->update([
            'status' => 'declined',
        ]);

        return new TrainerRequestResource($trainerRequest->load(['user', 'membership', 'trainerProfile.user', 'acceptedByTrainer.user', 'service', 'club']));
    }

    /**
     * Cancel a trainer request.
     */
    public function cancel(TrainerRequest $trainerRequest)
    {
        $user = Auth::user();
        if ($trainerRequest->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($trainerRequest->status !== 'pending') {
            return response()->json(['message' => 'Cannot cancel a non-pending request'], 400);
        }

        $trainerRequest->update([
            'status' => 'cancelled',
        ]);

        return new TrainerRequestResource($trainerRequest);
    }
}
