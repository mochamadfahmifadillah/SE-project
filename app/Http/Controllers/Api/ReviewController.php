<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Software;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * GET /api/v1/software/{software}/reviews
     *
     * Get published reviews for a software.
     */
    public function index(Software $software): JsonResponse
    {
        $reviews = Review::query()
            ->where('software_id', $software->id)
            ->whereIn('status', ['approved', 'published'])
            ->with([
                'user:id,name',
            ])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * POST /api/v1/software/{software}/reviews
     *
     * Submit a review.
     */
    public function store(
        Request $request,
        Software $software
    ): JsonResponse {
        $validated = $request->validate([
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],
            'content' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
        ]);

        $review = Review::create([
            'user_id' => $request->user()->id,
            'software_id' => $software->id,
            'rating' => $validated['rating'],
            'content' => $validated['content'],
            'status' => 'pending',
        ]);

        $review->load('user:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully and is awaiting moderation.',
            'data' => $review,
        ], 201);
    }

    /**
     * DELETE /api/v1/reviews/{review}
     *
     * Delete user's own review.
     */
    public function destroy(
        Request $request,
        Review $review
    ): JsonResponse {
        if ($review->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this review.',
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
        ]);
    }
}