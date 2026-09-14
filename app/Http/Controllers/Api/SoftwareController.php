<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Software;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SoftwareController extends Controller
{
    /**
     * GET /api/v1/software
     *
     * Get active software with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Software::query()
            ->where('is_active', true);

        // Search by software name, category, or description
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('category', 'ILIKE', "%{$search}%")
                    ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        // Filter by category
        if (
            $request->filled('category') &&
            $request->input('category') !== 'All'
        ) {
            $query->where(
                'category',
                $request->input('category')
            );
        }

        $software = $query
            ->orderByDesc('views')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $software,
        ]);
    }

    /**
     * GET /api/v1/software/{slug}
     *
     * Get software detail by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $software = Software::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$software) {
            return response()->json([
                'success' => false,
                'message' => 'Software not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $software,
        ]);
    }
}