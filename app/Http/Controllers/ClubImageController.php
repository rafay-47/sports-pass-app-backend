<?php

namespace App\Http\Controllers;

use App\Models\ClubImage;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Requests\StoreClubImageRequest;
use App\Http\Requests\UpdateClubImageRequest;
use App\Http\Controllers\UploadController;

class ClubImageController extends Controller
{
    /**
     * Display a listing of club images.
     */
    public function index(Request $request)
    {
        $query = ClubImage::with('club');

        // Filter by club
        if ($request->has('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by is_primary
        if ($request->has('is_primary')) {
            $query->where('is_primary', $request->boolean('is_primary'));
        }

        $images = $query->orderBy('display_order')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => [
                'images' => $images->items(),
                'pagination' => [
                    'current_page' => $images->currentPage(),
                    'last_page' => $images->lastPage(),
                    'per_page' => $images->perPage(),
                    'total' => $images->total(),
                ]
            ]
        ]);
    }

    /**
     * Store a newly created club image.
     */
    public function store(StoreClubImageRequest $request)
    {
        $club = Club::find($request->club_id);

        // Handle file upload using unified upload API
        if ($request->hasFile('image')) {
            $uploadResponse = app(UploadController::class)->upload(new Request([
                'file' => $request->file('image'),
                'type' => 'club_image',
                'related_id' => $request->club_id, // Use club_id as related_id for now, will update after creation
                'file_type' => 'image',
            ]));

            if ($uploadResponse->getStatusCode() !== 200) {
                return $uploadResponse; // Return error if upload fails
            }

            $uploadData = json_decode($uploadResponse->getContent(), true);
            $imageUrl = $uploadData['data']['url'];
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Image file is required'
            ], 422);
        }

        // If setting as primary, unset other primary images for this club
        if ($request->boolean('is_primary')) {
            ClubImage::where('club_id', $request->club_id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        $image = ClubImage::create([
            'club_id' => $request->club_id,
            'image_url' => $imageUrl,
            'alt_text' => $request->alt_text,
            'is_primary' => $request->boolean('is_primary'),
            'display_order' => $request->sort_order ?? 0,
        ]);

        // Update the upload with the actual image ID
        app(UploadController::class)->upload(new Request([
            'file' => $request->file('image'),
            'type' => 'club_image',
            'related_id' => $image->id,
            'file_type' => 'image',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Club image uploaded successfully',
            'data' => $image->load('club')
        ], 201);
    }

    /**
     * Display the specified club image.
     */
    public function show(ClubImage $clubImage)
    {
        $clubImage->load('club');

        return response()->json([
            'status' => 'success',
            'data' => $clubImage
        ]);
    }

    /**
     * Update the specified club image.
     */
    public function update(UpdateClubImageRequest $request, ClubImage $clubImage)
    {
        // If setting as primary, unset other primary images for this club
        if ($request->boolean('is_primary') && !$clubImage->is_primary) {
            ClubImage::where('club_id', $clubImage->club_id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        $clubImage->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Club image updated successfully',
            'data' => $clubImage->load('club')
        ]);
    }

    /**
     * Remove the specified club image.
     */
    public function destroy(ClubImage $clubImage)
    {
        // Delete the file from storage
        if (Storage::disk('public')->exists($clubImage->image_url)) {
            Storage::disk('public')->delete($clubImage->image_url);
        }

        $clubImage->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Club image deleted successfully'
        ]);
    }

    /**
     * Get images for a specific club.
     */
    public function getByClub(Club $club, Request $request)
    {
        $query = $club->images();

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by primary
        if ($request->has('primary_only')) {
            $query->where('is_primary', true);
        }

        $images = $query->orderBy('display_order')->get();

        return response()->json([
            'status' => 'success',
            'data' => $images
        ]);
    }

    /**
     * Set an image as primary.
     */
    public function setPrimary(ClubImage $clubImage)
    {
        // Unset other primary images for this club
        ClubImage::where('club_id', $clubImage->club_id)
            ->where('is_primary', true)
            ->update(['is_primary' => false]);

        // Set this image as primary
        $clubImage->update(['is_primary' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Primary image updated successfully',
            'data' => $clubImage->load('club')
        ]);
    }

    /**
     * Update sort order of images.
     */
    public function updateSortOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array',
            'images.*.id' => 'required|uuid|exists:club_images,id',
            'images.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->images as $imageData) {
            ClubImage::where('id', $imageData['id'])
                ->update(['display_order' => $imageData['sort_order']]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Sort order updated successfully'
        ]);
    }

    /**
     * Upload multiple images for a club.
     */
    public function bulkUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'club_id' => 'required|uuid|exists:clubs,id',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'type' => 'nullable|in:gallery,logo,banner,interior,exterior',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $club = Club::find($request->club_id);
        $uploadedImages = [];

        foreach ($request->file('images') as $file) {
            // Create a temporary ClubImage record first to get the ID
            $tempImage = ClubImage::create([
                'club_id' => $request->club_id,
                'image_url' => '', // Will be updated after upload
                'alt_text' => $request->alt_text,
                'is_primary' => false,
                'display_order' => 0,
            ]);

            // Upload using unified API
            $uploadResponse = app(UploadController::class)->upload(new Request([
                'file' => $file,
                'type' => 'club_image',
                'related_id' => $tempImage->id,
                'file_type' => 'image',
            ]));

            if ($uploadResponse->getStatusCode() === 200) {
                $uploadData = json_decode($uploadResponse->getContent(), true);
                $tempImage->update(['image_url' => $uploadData['data']['url']]);
                $uploadedImages[] = $tempImage;
            } else {
                // Delete the temp record if upload failed
                $tempImage->delete();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => count($uploadedImages) . ' images uploaded successfully',
            'data' => $uploadedImages
        ], 201);
    }

    /**
     * Get image statistics for clubs.
     */
    public function statistics()
    {
        $stats = [
            'total_images' => ClubImage::count(),
            'images_by_type' => ClubImage::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'clubs_with_images' => ClubImage::distinct('club_id')->count('club_id'),
            'average_images_per_club' => ClubImage::selectRaw('COUNT(*) / COUNT(DISTINCT club_id) as average')
                ->first()->average ?? 0,
            'total_storage_used' => ClubImage::sum('file_size'),
            'primary_images_count' => ClubImage::where('is_primary', true)->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}
