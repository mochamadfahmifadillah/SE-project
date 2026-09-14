<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comparison;
use App\Models\Software;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    /**
     * Get comparisons belonging to authenticated user.
     *
     * GET /api/v1/me/comparisons
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $comparisons = Comparison::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $data = $comparisons->map(function (Comparison $comparison) {
            $softwareIds = is_array($comparison->software_ids)
                ? $comparison->software_ids
                : [];

            $software = Software::query()
                ->whereIn('id', $softwareIds)
                ->where('is_active', true)
                ->get();

            return [
                'id' => $comparison->id,
                'name' => $comparison->name,
                'software_ids' => $softwareIds,
                'software' => $software,
                'created_at' => $comparison->created_at,
                'updated_at' => $comparison->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Create a comparison for authenticated user.
     *
     * POST /api/v1/compare
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'software_ids' => [
                'required',
                'array',
                'min:2',
                'max:3',
            ],

            'software_ids.*' => [
                'integer',
                'distinct',
                'exists:software,id',
            ],

            'name' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $user = $request->user();

        $softwareIds = $validated['software_ids'];

        /*
        |--------------------------------------------------------------------------
        | Make sure selected software is active
        |--------------------------------------------------------------------------
        */

        $software = Software::query()
            ->whereIn('id', $softwareIds)
            ->where('is_active', true)
            ->get();

        if ($software->count() < 2) {
            return response()->json([
                'success' => false,
                'message' => 'At least two active software products are required.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Create comparison
        |--------------------------------------------------------------------------
        */

        $comparison = Comparison::create([
            'user_id' => $user->id,
            'software_ids' => $softwareIds,
            'name' => $validated['name'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Software comparison created successfully.',
            'data' => [
                'comparison' => [
                    'id' => $comparison->id,
                    'name' => $comparison->name,
                    'software_ids' => $comparison->software_ids,
                    'software' => $software,
                    'created_at' => $comparison->created_at,
                    'updated_at' => $comparison->updated_at,
                ],
            ],
        ], 201);
    }
}