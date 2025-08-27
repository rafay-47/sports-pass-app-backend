<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FacilityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Facility::query();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'ILIKE', "%{$search}%");
        }

        $perPage = $request->get('per_page', 15);
        $facilities = $query->orderBy('name')->paginate($perPage);

        return response()->json(['status' => 'success', 'data' => ['facilities' => $facilities]]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->user()->user_role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can create facilities'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200|unique:facilities,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $facility = Facility::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true),
                'created_by' => $request->user()->id
            ]);

            return response()->json(['status' => 'success', 'message' => 'Facility created successfully', 'data' => ['facility' => $facility]], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to create facility', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Facility $facility): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => ['facility' => $facility]]);
    }

    public function update(Request $request, Facility $facility): JsonResponse
    {
        if ($request->user()->user_role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can update facilities'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:200|unique:facilities,name,' . $facility->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $facility->update($request->all());

            return response()->json(['status' => 'success', 'message' => 'Facility updated successfully', 'data' => ['facility' => $facility->fresh()]]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to update facility', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, Facility $facility): JsonResponse
    {
        if ($request->user()->user_role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can delete facilities'], 403);
        }

        try {
            $facility->delete();

            return response()->json(['status' => 'success', 'message' => 'Facility deleted successfully']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to delete facility', 'error' => $e->getMessage()], 500);
        }
    }
}
