<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SoftwareFeature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminFeatureController extends Controller
{
    /**
     * GET /api/v1/admin/features
     */
    public function index(): JsonResponse
    {
        $features = SoftwareFeature::query()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $features,
        ]);
    }

    /**
     * POST /api/v1/admin/features
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:software_features,slug',
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $validated['slug']
            ?? Str::slug($validated['name']);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $feature = SoftwareFeature::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Feature created successfully.',
            'data' => $feature,
        ], 201);
    }

    /**
     * GET /api/v1/admin/features/{feature}
     */
    public function show(SoftwareFeature $feature): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $feature,
        ]);
    }

    /**
     * PUT /api/v1/admin/features/{feature}
     */
    public function update(
        Request $request,
        SoftwareFeature $feature
    ): JsonResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:software_features,slug,' . $feature->id,
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $feature->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Feature updated successfully.',
            'data' => $feature->fresh(),
        ]);
    }

    /**
     * DELETE /api/v1/admin/features/{feature}
     */
    public function destroy(SoftwareFeature $feature): JsonResponse
    {
        $feature->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feature deleted successfully.',
        ]);
    }
}