<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Examples:
     *
     * ->middleware('permission:dashboard.view')
     *
     * ->middleware('permission:software.view,software.create')
     *
     * The user is allowed when they have at least one
     * of the requested permissions through their roles.
     */
    public function handle(
        Request $request,
        Closure $next,
        ...$permissions
    ): Response {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Authentication Check
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize Permissions
        |--------------------------------------------------------------------------
        |
        | Supports:
        |
        | permission:software.view
        |
        | permission:software.view,software.create
        |
        */

        $permissions = collect($permissions)
            ->flatMap(function ($permission) {
                return explode(',', $permission);
            })
            ->map(function ($permission) {
                return trim($permission);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Permission Parameter Check
        |--------------------------------------------------------------------------
        */

        if (empty($permissions)) {
            return response()->json([
                'success' => false,
                'message' => 'No permission specified.',
            ], 500);
        }

        /*
        |--------------------------------------------------------------------------
        | Permission Check
        |--------------------------------------------------------------------------
        |
        | A user is allowed when they have at least ONE
        | of the requested permissions through one of
        | their assigned roles.
        |
        */

        $hasPermission = $user
            ->roles()
            ->whereHas('permissions', function ($query) use ($permissions) {
                $query->whereIn('name', $permissions);
            })
            ->exists();

        /*
        |--------------------------------------------------------------------------
        | Forbidden
        |--------------------------------------------------------------------------
        */

        if (!$hasPermission) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You do not have the required permission.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Authorized
        |--------------------------------------------------------------------------
        */

        return $next($request);
    }
}