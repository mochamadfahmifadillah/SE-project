<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminVendorController extends Controller
{
    /**
     * GET /api/v1/admin/vendors
     */
    public function index(): JsonResponse
    {
        $vendors = Vendor::query()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $vendors,
        ]);
    }

    /**
     * POST /api/v1/admin/vendors
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:vendors,slug'],
            'website' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $validated['slug']
            ?? Str::slug($validated['name']);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $vendor = Vendor::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vendor created successfully.',
            'data' => $vendor,
        ], 201);
    }

    /**
     * GET /api/v1/admin/vendors/{vendor}
     */
    public function show(Vendor $vendor): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $vendor,
        ]);
    }

    /**
     * PUT /api/v1/admin/vendors/{vendor}
     */
    public function update(
        Request $request,
        Vendor $vendor
    ): JsonResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:vendors,slug,' . $vendor->id,
            ],
            'website' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $vendor->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vendor updated successfully.',
            'data' => $vendor->fresh(),
        ]);
    }

    /**
     * DELETE /api/v1/admin/vendors/{vendor}
     */
    public function destroy(Vendor $vendor): JsonResponse
    {
        $vendor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vendor deleted successfully.',
        ]);
    }
}