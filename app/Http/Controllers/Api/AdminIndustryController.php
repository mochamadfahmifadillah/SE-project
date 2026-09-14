<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminIndustryController extends Controller
{
    /**
     * GET /api/v1/admin/industries
     */
    public function index(): JsonResponse
    {
        $industries = Industry::query()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $industries,
        ]);
    }

    /**
     * POST /api/v1/admin/industries
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:industries,slug'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $validated['slug']
            ?? Str::slug($validated['name']);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $industry = Industry::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Industry created successfully.',
            'data' => $industry,
        ], 201);
    }

    /**
     * GET /api/v1/admin/industries/{industry}
     */
    public function show(Industry $industry): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $industry,
        ]);
    }

    /**
     * PUT /api/v1/admin/industries/{industry}
     */
    public function update(
        Request $request,
        Industry $industry
    ): JsonResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:industries,slug,' . $industry->id,
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $industry->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Industry updated successfully.',
            'data' => $industry->fresh(),
        ]);
    }

    /**
     * DELETE /api/v1/admin/industries/{industry}
     */
    public function destroy(Industry $industry): JsonResponse
    {
        $industry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Industry deleted successfully.',
        ]);
    }
}