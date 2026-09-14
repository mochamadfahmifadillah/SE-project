<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessSize;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBusinessSizeController extends Controller
{
    /**
     * GET /api/v1/admin/business-sizes
     */
    public function index(): JsonResponse
    {
        $businessSizes = BusinessSize::query()
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $businessSizes,
        ]);
    }

    /**
     * POST /api/v1/admin/business-sizes
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:business_sizes,name',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:business_sizes,slug',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $businessSize = BusinessSize::create([
            'name' => $validated['name'],
            'slug' => $validated['slug']
                ?? Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Business size created successfully.',
            'data' => $businessSize,
        ], 201);
    }

    /**
     * GET /api/v1/admin/business-sizes/{businessSize}
     */
    public function show(BusinessSize $businessSize): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $businessSize,
        ]);
    }

    /**
     * PUT /api/v1/admin/business-sizes/{businessSize}
     */
    public function update(
        Request $request,
        BusinessSize $businessSize
    ): JsonResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:business_sizes,name,' . $businessSize->id,
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:business_sizes,slug,' . $businessSize->id,
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $businessSize->update([
            'name' => $validated['name'],
            'slug' => $validated['slug']
                ?? Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active']
                ?? $businessSize->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Business size updated successfully.',
            'data' => $businessSize->fresh(),
        ]);
    }

    /**
     * DELETE /api/v1/admin/business-sizes/{businessSize}
     */
    public function destroy(
        BusinessSize $businessSize
    ): JsonResponse {
        $businessSize->delete();

        return response()->json([
            'success' => true,
            'message' => 'Business size deleted successfully.',
        ]);
    }
}