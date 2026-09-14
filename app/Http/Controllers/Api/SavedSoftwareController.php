<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavedSoftware;
use App\Models\Software;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedSoftwareController extends Controller
{
    /**
     * Get authenticated user's saved software.
     *
     * GET /api/v1/me/saved-software
     */
    public function index(Request $request): JsonResponse
    {
        $savedSoftware = SavedSoftware::with('software')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $savedSoftware->map(function ($item) {
                return [
                    'id' => $item->software->id,
                    'name' => $item->software->name,
                    'slug' => $item->software->slug,
                    'category' => $item->software->category,
                    'rating' => $item->software->rating,
                    'price' => $item->software->price,
                    'fit' => $item->software->fit,
                    'tag' => $item->software->tag,
                    'description' => $item->software->description,
                    'views' => $item->software->views,
                    'saved_at' => $item->created_at,
                ];
            }),
        ]);
    }

    /**
     * Save software.
     *
     * POST /api/v1/me/saved-software
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'software_id' => ['required', 'integer', 'exists:software,id'],
        ]);

        $user = $request->user();

        $saved = SavedSoftware::firstOrCreate([
            'user_id' => $user->id,
            'software_id' => $validated['software_id'],
        ]);

        $saved->load('software');

        return response()->json([
            'success' => true,
            'message' => 'Software saved successfully.',
            'data' => [
                'id' => $saved->software->id,
                'name' => $saved->software->name,
                'slug' => $saved->software->slug,
                'category' => $saved->software->category,
                'rating' => $saved->software->rating,
                'price' => $saved->software->price,
                'fit' => $saved->software->fit,
                'tag' => $saved->software->tag,
                'description' => $saved->software->description,
                'views' => $saved->software->views,
            ],
        ], 201);
    }

    /**
     * Remove saved software.
     *
     * DELETE /api/v1/me/saved-software/{software}
     */
    public function destroy(Request $request, Software $software): JsonResponse
    {
        $deleted = SavedSoftware::where('user_id', $request->user()->id)
            ->where('software_id', $software->id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Software is not saved.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Software removed from saved list.',
        ]);
    }
}