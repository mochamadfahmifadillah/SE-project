<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Software;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminSoftwareController extends Controller
{
    /**
     * GET /api/v1/admin/software
     */
    public function index(Request $request): JsonResponse
    {
        $query = Software::query();

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('category', 'ILIKE', "%{$search}%")
                    ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            }

            if ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $software = $query
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $software,
        ]);
    }

    /**
     * POST /api/v1/admin/software
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:software,slug'],
            'category' => ['required', 'string', 'max:255'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'price' => ['nullable', 'string', 'max:255'],
            'views' => ['nullable', 'integer', 'min:0'],
            'fit' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['rating'] = $validated['rating'] ?? 0;
        $validated['views'] = $validated['views'] ?? 0;

        $software = Software::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Software created successfully.',
            'data' => $software,
        ], 201);
    }

    /**
     * GET /api/v1/admin/software/{software}
     */
    public function show(Software $software): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $software,
        ]);
    }

    /**
     * PUT /api/v1/admin/software/{software}
     */
    public function update(
        Request $request,
        Software $software
    ): JsonResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:software,slug,' . $software->id,
            ],
            'category' => ['required', 'string', 'max:255'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'price' => ['nullable', 'string', 'max:255'],
            'views' => ['nullable', 'integer', 'min:0'],
            'fit' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $software->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Software updated successfully.',
            'data' => $software->fresh(),
        ]);
    }

    /**
     * DELETE /api/v1/admin/software/{software}
     */
    public function destroy(Software $software): JsonResponse
    {
        $software->delete();

        return response()->json([
            'success' => true,
            'message' => 'Software deleted successfully.',
        ]);
    }
}