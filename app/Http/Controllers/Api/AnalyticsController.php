<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comparison;
use App\Models\ImplementationRequest;
use App\Models\Partner;
use App\Models\Review;
use App\Models\Software;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    /**
     * Get admin analytics overview.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Analytics data retrieved successfully.',
            'data' => [
                'overview' => [
                    'total_software' => Software::count(),
                    'total_users' => User::count(),
                    'total_reviews' => Review::count(),
                    'total_implementation_requests' => ImplementationRequest::count(),
                    'total_vendors' => Vendor::count(),
                    'total_partners' => Partner::count(),
                    'total_articles' => Article::count(),
                    'total_comparisons' => Comparison::count(),
                ],
            ],
        ]);
    }
}