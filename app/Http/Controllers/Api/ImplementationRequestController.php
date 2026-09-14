<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImplementationRequest;
use App\Models\Software;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImplementationRequestController extends Controller
{
    /**
     * Get authenticated user's implementation requests.
     *
     * GET /api/v1/me/implementation-requests
     */
    public function index(Request $request): JsonResponse
    {
        $requests = ImplementationRequest::query()
            ->with('software')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    /**
     * Create implementation request.
     *
     * POST /api/v1/implementation-requests
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'contact_name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'software_id' => [
                'nullable',
                'integer',
                'exists:software,id',
            ],

            'message' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Validate Active Software
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['software_id'])) {
            $softwareExists = Software::query()
                ->where('id', $validated['software_id'])
                ->where('is_active', true)
                ->exists();

            if (!$softwareExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected software is not available.',
                ], 422);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Create Implementation Request
        |--------------------------------------------------------------------------
        */

        $implementationRequest = ImplementationRequest::create([
            'user_id' => $user->id,
            'company_name' => $validated['company_name'] ?? null,
            'contact_name' => $validated['contact_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'software_id' => $validated['software_id'] ?? null,
            'message' => $validated['message'] ?? null,

            // Lead management
            'status' => 'NEW',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Load Software
        |--------------------------------------------------------------------------
        */

        $implementationRequest->load('software');

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Implementation request submitted successfully.',
            'data' => [
                'implementation_request' => $implementationRequest,
            ],
        ], 201);
    }
}