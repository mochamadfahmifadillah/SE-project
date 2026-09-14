<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    /**
     * GET /api/v1/admin/reviews
     *
     * Get all reviews for admin.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::query()
            ->with([
                'user:id,name,email',
                'software:id,name,slug',
            ])
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Filter Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('software', function ($softwareQuery) use ($search) {
                        $softwareQuery
                            ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $reviews = $query
            ->paginate($request->integer('per_page', 15));

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [
            'pending' => Review::where('status', 'pending')->count(),

            'approved' => Review::whereIn(
                'status',
                ['approved', 'published']
            )->count(),

            'rejected' => Review::where('status', 'rejected')->count(),

            'total' => Review::count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * GET /api/v1/admin/reviews/{review}
     *
     * Get review detail.
     */
    public function show(Review $review): JsonResponse
    {
        $review->load([
            'user:id,name,email',
            'software:id,name,slug',
        ]);

        return response()->json([
            'success' => true,
            'data' => $review,
        ]);
    }

    /**
     * PUT /api/v1/admin/reviews/{review}
     *
     * Update / moderate review.
     */
    public function update(
        Request $request,
        Review $review
    ): JsonResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                'in:pending,approved,published,rejected',
            ],
        ]);

        $review->update([
            'status' => $validated['status'],
        ]);

        $review->load([
            'user:id,name,email',
            'software:id,name,slug',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review status updated successfully.',
            'data' => $review,
        ]);
    }

    /**
     * DELETE /api/v1/admin/reviews/{review}
     *
     * Delete review.
     */
    public function destroy(Review $review): JsonResponse
    {
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
        ]);
    }
}