<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    |
    | GET /api/v1/admin/audit-logs
    |
    */

    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query()
            ->with([
                'user:id,name,email',
            ])
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Action Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('action')) {
            $query->where(
                'action',
                $request->string('action')->toString()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | User Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('user_id')) {
            $query->where(
                'user_id',
                $request->integer('user_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Auditable Type Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('auditable_type')) {
            $query->where(
                'auditable_type',
                'like',
                '%' . $request->string('auditable_type')->toString() . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = min(
            max($request->integer('per_page', 15), 1),
            100
        );

        $logs = $query->paginate($perPage);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'data' => $logs->items(),

            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    |
    | GET /api/v1/admin/audit-logs/{id}
    |
    */

    public function show(int $id): JsonResponse
    {
        $log = AuditLog::with([
            'user:id,name,email',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $log,
        ]);
    }
}