<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Integrations;
use Illuminate\Http\Request;

class AdminIntegrationController extends Controller
{
    /**
     * GET /api/v1/admin/integrations
     */
    public function index()
    {
        $integrations = Integrations::with('software')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $integrations,
            'meta' => [
                'total' => $integrations->count(),
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/integrations
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'software_id' => [
                'required',
                'exists:software,id',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'url' => [
                'nullable',
                'url',
                'max:2048',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $integration = Integrations::create([
            'software_id' => $validated['software_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'url' => $validated['url'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Integration created successfully.',
            'data' => $integration->load('software'),
        ], 201);
    }

    /**
     * GET /api/v1/admin/integrations/{integration}
     */
    public function show(Integrations $integration)
    {
        return response()->json([
            'success' => true,
            'data' => $integration->load('software'),
        ]);
    }

    /**
     * PUT /api/v1/admin/integrations/{integration}
     */
    public function update(
        Request $request,
        Integrations $integration
    ) {
        $validated = $request->validate([
            'software_id' => [
                'sometimes',
                'required',
                'exists:software,id',
            ],
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
            'url' => [
                'nullable',
                'url',
                'max:2048',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $integration->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Integration updated successfully.',
            'data' => $integration->fresh()->load('software'),
        ]);
    }

    /**
     * DELETE /api/v1/admin/integrations/{integration}
     */
    public function destroy(Integrations $integration)
    {
        $integration->delete();

        return response()->json([
            'success' => true,
            'message' => 'Integration deleted successfully.',
        ]);
    }
}