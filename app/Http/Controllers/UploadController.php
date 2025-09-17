<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UploadController extends Controller
{
    protected $supabaseUrl;
    protected $supabaseKey;
    protected $bucketName = 'Sports Pass Backend bucket'; 

    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseKey = env('SUPABASE_ANON_KEY');
    }

    /**
     * Unified file upload endpoint.
     * Handles uploads for users, sports, sport_services, tiers, trainer_certifications, club_images.
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:5120', // 5MB max
            'type' => 'required|in:user_profile,sport_icon,service_icon,tier_icon,certificate,club_image',
            'related_id' => 'required|uuid', // e.g., user_id, sport_id, etc.
            'file_type' => 'nullable|in:image,document', // Auto-detected if not provided
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');
        $type = $request->type;
        $relatedId = $request->related_id;
        $fileType = $request->file_type ?? ($file->getMimeType() === 'application/pdf' ? 'document' : 'image');

        // Validate file type based on category
        if ($fileType === 'image' && !in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid image file type'], 400);
        }
        if ($fileType === 'document' && !in_array($file->getMimeType(), ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid document file type'], 400);
        }

        // Generate unique filename
        $filename = $relatedId . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = "uploads/{$type}/{$filename}";

        try {
            // Upload to Supabase Storage via REST API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->supabaseKey,
                'Content-Type' => $file->getMimeType(),
            ])->withBody(file_get_contents($file->getRealPath()), $file->getMimeType())
            ->post("{$this->supabaseUrl}/storage/v1/object/{$this->bucketName}/{$path}");

            if (!$response->successful()) {
                Log::error('Supabase upload failed', [
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Upload failed: ' . $response->body()
                ], 500);
            }

            // Get public URL
            $url = "{$this->supabaseUrl}/storage/v1/object/public/{$this->bucketName}/{$path}";

            // Update the relevant table based on type
            $this->updateRelatedTable($type, $relatedId, $url);

            return response()->json([
                'status' => 'success',
                'message' => 'File uploaded successfully',
                'data' => [
                    'url' => $url,
                    'file_type' => $fileType,
                    'size' => $file->getSize(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('File upload exception', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the related table with the file URL.
     */
    private function updateRelatedTable($type, $relatedId, $url)
    {
        switch ($type) {
            case 'user_profile':
                \App\Models\User::where('id', $relatedId)->update(['profile_image_url' => $url]);
                break;
            case 'sport_icon':
                \App\Models\Sport::where('id', $relatedId)->update(['icon' => $url]);
                break;
            case 'service_icon':
                \App\Models\SportService::where('id', $relatedId)->update(['icon' => $url]);
                break;
            case 'tier_icon':
                \App\Models\Tier::where('id', $relatedId)->update(['icon' => $url]);
                break;
            case 'certificate':
                \App\Models\TrainerCertification::where('id', $relatedId)->update(['certificate_url' => $url]);
                break;
            case 'club_image':
                \App\Models\ClubImage::where('id', $relatedId)->update(['image_url' => $url]);
                break;
        }
    }
}