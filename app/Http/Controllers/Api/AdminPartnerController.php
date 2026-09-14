<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class AdminPartnerController extends Controller
{
    /**
     * GET /api/v1/admin/partners
     */
    public function index()
    {
        $partners = Partner::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $partners,
            'meta' => [
                'total' => $partners->count(),
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/partners
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'website' => [
                'nullable',
                'url',
                'max:2048',
            ],
            'contact_email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'logo' => [
                'nullable',
                'string',
                'max:2048',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $partner = Partner::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'website' => $validated['website'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'logo' => $validated['logo'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Partner created successfully.',
            'data' => $partner,
        ], 201);
    }

    /**
     * GET /api/v1/admin/partners/{partner}
     */
    public function show(Partner $partner)
    {
        return response()->json([
            'success' => true,
            'data' => $partner,
        ]);
    }

    /**
     * PUT /api/v1/admin/partners/{partner}
     */
    public function update(
        Request $request,
        Partner $partner
    ) {
        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'website' => [
                'nullable',
                'url',
                'max:2048',
            ],
            'contact_email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'logo' => [
                'nullable',
                'string',
                'max:2048',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $partner->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Partner updated successfully.',
            'data' => $partner->fresh(),
        ]);
    }

    /**
     * DELETE /api/v1/admin/partners/{partner}
     */
    public function destroy(Partner $partner)
    {
        $partner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Partner deleted successfully.',
        ]);
    }
}