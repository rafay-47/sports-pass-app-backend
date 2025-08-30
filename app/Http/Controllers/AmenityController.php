<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AmenityController extends Controller
{
    /**
     * List amenities with optional filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Amenity::query();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'ILIKE', "%{$search}%");
        }

        $amenities = $query->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'amenities' => $amenities
            ]
        ]);
    }

    /**
     * Store a new amenity (admin only).
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->user()->user_role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can create amenities'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200|unique:amenities,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $amenity = Amenity::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true),
                'created_by' => $request->user()->id
            ]);

            return response()->json(['status' => 'success', 'message' => 'Amenity created successfully', 'data' => ['amenity' => $amenity]], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to create amenity', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a single amenity.
     */
    public function show(Amenity $amenity): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => ['amenity' => $amenity]]);
    }

    /**
     * Update an amenity (admin only).
     */
    public function update(Request $request, Amenity $amenity): JsonResponse
    {
        if ($request->user()->user_role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can update amenities'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:200|unique:amenities,name,' . $amenity->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $amenity->update($request->all());

            return response()->json(['status' => 'success', 'message' => 'Amenity updated successfully', 'data' => ['amenity' => $amenity->fresh()]]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to update amenity', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete an amenity (admin only).
     */
    public function destroy(Request $request, Amenity $amenity): JsonResponse
    {
        if ($request->user()->user_role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can delete amenities'], 403);
        }

        try {
            $amenity->delete();

            return response()->json(['status' => 'success', 'message' => 'Amenity deleted successfully']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to delete amenity', 'error' => $e->getMessage()], 500);
        }
    }
}
