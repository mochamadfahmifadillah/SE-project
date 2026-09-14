<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comparison;
use App\Models\ImplementationRequest;
use App\Models\SavedSoftware;
use App\Models\Software;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    /**
     * GET /api/v1/admin/dashboard
     */
    public function index(): JsonResponse
    {
        $totalSoftware = Software::count();

        $activeSoftware = Software::where('is_active', true)->count();

        $totalUsers = User::count();

        $totalComparisons = Comparison::count();

        $totalSavedSoftware = SavedSoftware::count();

        $totalImplementationRequests = ImplementationRequest::count();

        $pendingImplementationRequests = ImplementationRequest::where(
            'status',
            'pending'
        )->count();

        /*
        |--------------------------------------------------------------------------
        | Popular Software
        |--------------------------------------------------------------------------
        */

        $popularSoftware = Software::query()
            ->where('is_active', true)
            ->orderByDesc('views')
            ->limit(5)
            ->get([
                'id',
                'name',
                'slug',
                'category',
                'rating',
                'views',
                'tag',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Recent Users
        |--------------------------------------------------------------------------
        */

        $recentUsers = User::query()
            ->latest()
            ->limit(5)
            ->get([
                'id',
                'name',
                'email',
                'created_at',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Recent Implementation Requests
        |--------------------------------------------------------------------------
        */

        $recentImplementationRequests = ImplementationRequest::query()
            ->with('software:id,name,slug')
            ->latest()
            ->limit(5)
            ->get([
                'id',
                'user_id',
                'company_name',
                'contact_name',
                'email',
                'phone',
                'software_id',
                'message',
                'status',
                'created_at',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Recent Comparisons
        |--------------------------------------------------------------------------
        */

        $recentComparisons = Comparison::query()
            ->with('user:id,name,email')
            ->latest()
            ->limit(5)
            ->get([
                'id',
                'user_id',
                'name',
                'software_ids',
                'created_at',
            ]);

        return response()->json([
            'success' => true,

            'data' => [
                'summary' => [
                    'total_software' => $totalSoftware,
                    'active_software' => $activeSoftware,
                    'total_users' => $totalUsers,
                    'total_comparisons' => $totalComparisons,
                    'total_saved_software' => $totalSavedSoftware,
                    'total_implementation_requests' => $totalImplementationRequests,
                    'pending_implementation_requests' => $pendingImplementationRequests,
                ],

                'popular_software' => $popularSoftware,

                'recent_users' => $recentUsers,

                'recent_implementation_requests' => $recentImplementationRequests,

                'recent_comparisons' => $recentComparisons,
            ],
        ]);
    }
}