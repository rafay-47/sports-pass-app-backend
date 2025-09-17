<?php

namespace App\Http\Controllers;

use App\Models\TrainerCertification;
use App\Models\TrainerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class TrainerCertificationController extends Controller
{
    /**
     * Display a listing of certifications.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TrainerCertification::with(['trainerProfile.user:id,name,email']);

        // Filter by trainer profile
        if ($request->filled('trainer_profile_id')) {
            $query->where('trainer_profile_id', $request->trainer_profile_id);
        }

        // Filter by verification status
        if ($request->has('verified')) {
            $query->where('is_verified', $request->boolean('verified'));
        }

        // Filter by expiry status
        if ($request->boolean('expired_only')) {
            $query->expired();
        } elseif ($request->boolean('valid_only')) {
            $query->valid();
        }

        // Filter by expiring soon
        if ($request->boolean('expiring_soon')) {
            $query->whereNotNull('expiry_date')
                  ->whereRaw('expiry_date <= ?', [now()->addDays(30)->toDateString()]);
        }

        // Search by certification name or organization
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('certification_name', 'ILIKE', "%{$search}%")
                  ->orWhere('issuing_organization', 'ILIKE', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $certifications = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'certifications' => $certifications->items(),
                'pagination' => [
                    'current_page' => $certifications->currentPage(),
                    'last_page' => $certifications->lastPage(),
                    'per_page' => $certifications->perPage(),
                    'total' => $certifications->total(),
                ]
            ]
        ]);
    }

    /**
     * Store a newly created certification.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'trainer_profile_id' => 'required|exists:trainer_profiles,id',
            'certification_name' => 'required|string|max:200',
            'issuing_organization' => 'nullable|string|max:200',
            'issue_date' => 'nullable|date|before_or_equal:today',
            'expiry_date' => 'nullable|date|after:issue_date',
            'certificate_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'certificate_url' => 'nullable|url|max:500',
        ]);

        // Authorization check
        $trainerProfile = TrainerProfile::findOrFail($request->trainer_profile_id);
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && $trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to add certifications for this trainer'
            ], 403);
        }

        // Handle certificate file upload
        if ($request->hasFile('certificate_file')) {
            $uploadResponse = app(\App\Http\Controllers\UploadController::class)->upload(new Request([
                'file' => $request->file('certificate_file'),
                'type' => 'certificate',
                'related_id' => $request->trainer_profile_id, // Will update after creation
                'file_type' => 'document',
            ]));

            if ($uploadResponse->getStatusCode() !== 200) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Certificate upload failed'
                ], 500);
            }

            $uploadData = json_decode($uploadResponse->getContent(), true);
            $request->merge(['certificate_url' => $uploadData['data']['url']]);
        }

        $certification = TrainerCertification::create([
            'trainer_profile_id' => $request->trainer_profile_id,
            'certification_name' => $request->certification_name,
            'issuing_organization' => $request->issuing_organization,
            'issue_date' => $request->issue_date,
            'expiry_date' => $request->expiry_date,
            'certificate_url' => $request->certificate_url,
            'is_verified' => false, // Always start as unverified
        ]);

        // Update the upload with the actual certification ID
        if ($request->hasFile('certificate_file')) {
            app(\App\Http\Controllers\UploadController::class)->upload(new Request([
                'file' => $request->file('certificate_file'),
                'type' => 'certificate',
                'related_id' => $certification->id,
                'file_type' => 'document',
            ]));
        }

        $certification->load(['trainerProfile.user:id,name,email']);

        return response()->json([
            'status' => 'success',
            'message' => 'Certification added successfully',
            'data' => [
                'certification' => $certification
            ]
        ], 201);
    }

    /**
     * Display the specified certification.
     */
    public function show(TrainerCertification $trainerCertification): JsonResponse
    {
        $trainerCertification->load(['trainerProfile.user:id,name,email']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'certification' => $trainerCertification
            ]
        ]);
    }

    /**
     * Update the specified certification.
     */
    public function update(Request $request, TrainerCertification $trainerCertification): JsonResponse
    {
        $request->validate([
            'certification_name' => 'sometimes|required|string|max:200',
            'issuing_organization' => 'nullable|string|max:200',
            'issue_date' => 'nullable|date|before_or_equal:today',
            'expiry_date' => 'nullable|date|after:issue_date',
            'certificate_url' => 'nullable|url|max:500',
        ]);

        // Authorization check
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && $trainerCertification->trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to update this certification'
            ], 403);
        }

        $trainerCertification->update($request->only([
            'certification_name',
            'issuing_organization', 
            'issue_date',
            'expiry_date',
            'certificate_url'
        ]));

        $trainerCertification->load(['trainerProfile.user:id,name,email']);

        return response()->json([
            'status' => 'success',
            'message' => 'Certification updated successfully',
            'data' => [
                'certification' => $trainerCertification
            ]
        ]);
    }

    /**
     * Remove the specified certification.
     */
    public function destroy(Request $request, TrainerCertification $trainerCertification): JsonResponse
    {
        // Authorization check
        $user = $request->user();
        
        if (!$user || (!in_array($user->user_role, ['admin', 'owner']) && $trainerCertification->trainerProfile->user_id !== $user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to delete this certification'
            ], 403);
        }

        $trainerCertification->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Certification deleted successfully'
        ]);
    }

    /**
     * Verify a certification.
     */
    public function verify(Request $request, TrainerCertification $trainerCertification): JsonResponse
    {
        // Only admins and owners can verify certifications
        $user = $request->user();
        
        if (!$user || !in_array($user->user_role, ['admin', 'owner'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to verify certifications'
            ], 403);
        }

        $trainerCertification->update(['is_verified' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Certification verified successfully',
            'data' => [
                'certification' => $trainerCertification
            ]
        ]);
    }

    /**
     * Unverify a certification.
     */
    public function unverify(Request $request, TrainerCertification $trainerCertification): JsonResponse
    {
        // Only admins and owners can unverify certifications
        $user = $request->user();
        
        if (!$user || !in_array($user->user_role, ['admin', 'owner'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to unverify certifications'
            ], 403);
        }

        $trainerCertification->update(['is_verified' => false]);

        return response()->json([
            'status' => 'success',
            'message' => 'Certification unverified successfully',
            'data' => [
                'certification' => $trainerCertification
            ]
        ]);
    }

    /**
     * Get certifications for a specific trainer.
     */
    public function getByTrainer(Request $request, TrainerProfile $trainerProfile): JsonResponse
    {
        $query = $trainerProfile->certifications();

        // Filter by verification status
        if ($request->has('verified')) {
            $query->where('is_verified', $request->boolean('verified'));
        }

        // Filter by validity
        if ($request->boolean('valid_only')) {
            $query->valid();
        }

        $certifications = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'trainer_profile' => $trainerProfile->only(['id', 'user_id']),
                'certifications' => $certifications
            ]
        ]);
    }
}
