<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImplementationRequest;
use App\Models\Software;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminLeadsController extends Controller
{
    /**
     * Lead status lifecycle.
     */
    private const STATUSES = [
        'NEW',
        'ASSIGNED',
        'CONTACTED',
        'QUALIFIED',
        'PROPOSAL_SENT',
        'WON',
        'LOST',
    ];

    /**
     * GET /api/v1/admin/leads
     *
     * Get implementation leads.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ImplementationRequest::query()
            ->with([
                'software:id,name,slug',
                'user:id,name,email',
                'assignedAdmin:id,name,email',
            ])
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'ILIKE', "%{$search}%")
                    ->orWhere('contact_name', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%")
                    ->orWhere('phone', 'ILIKE', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                strtoupper($request->input('status'))
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Software Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('software_id')) {
            $query->where(
                'software_id',
                $request->input('software_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Assigned Admin Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('assigned_to')) {
            $query->where(
                'assigned_to',
                $request->input('assigned_to')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = min(
            max((int) $request->input('per_page', 15), 1),
            100
        );

        $leads = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $leads->items(),
            'meta' => [
                'current_page' => $leads->currentPage(),
                'last_page' => $leads->lastPage(),
                'per_page' => $leads->perPage(),
                'total' => $leads->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/leads/{lead}
     *
     * Get lead detail.
     */
    public function show(ImplementationRequest $lead): JsonResponse
    {
        $lead->load([
            'software:id,name,slug',
            'user:id,name,email',
            'assignedAdmin:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'data' => $lead,
        ]);
    }

    /**
     * PUT /api/v1/admin/leads/{lead}
     *
     * Update lead information.
     */
    public function update(
        Request $request,
        ImplementationRequest $lead
    ): JsonResponse {
        $validated = $request->validate([
            'company_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'contact_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'sometimes',
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

            'status' => [
                'sometimes',
                Rule::in(self::STATUSES),
            ],

            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'lost_reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Software Validation
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists('software_id', $validated)
            && !empty($validated['software_id'])
        ) {
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
        | Assigned Admin Validation
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists('assigned_to', $validated)
            && !empty($validated['assigned_to'])
        ) {
            $admin = User::find($validated['assigned_to']);

            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assigned admin was not found.',
                ], 422);
            }

            if (!$admin->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected user does not have admin role.',
                ], 422);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Status Handling
        |--------------------------------------------------------------------------
        */

        if (
            isset($validated['status'])
            && $validated['status'] === 'LOST'
            && empty($validated['lost_reason'])
            && empty($lead->lost_reason)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Lost reason is required when lead status is LOST.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Automatic Assignment Status
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists('assigned_to', $validated)
            && !empty($validated['assigned_to'])
            && !isset($validated['status'])
            && $lead->status === 'NEW'
        ) {
            $validated['status'] = 'ASSIGNED';
        }

        /*
        |--------------------------------------------------------------------------
        | Lifecycle Timestamps
        |--------------------------------------------------------------------------
        */

        if (
            isset($validated['status'])
            && $validated['status'] !== $lead->status
        ) {
            switch ($validated['status']) {
                case 'QUALIFIED':
                    $validated['qualified_at'] =
                        $lead->qualified_at ?? now();
                    break;

                case 'PROPOSAL_SENT':
                    $validated['proposal_sent_at'] =
                        $lead->proposal_sent_at ?? now();
                    break;

                case 'WON':
                    $validated['closed_at'] =
                        $lead->closed_at ?? now();
                    break;

                case 'LOST':
                    $validated['closed_at'] =
                        $lead->closed_at ?? now();
                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Clear Lost Reason When Reopening Lead
        |--------------------------------------------------------------------------
        */

        if (
            isset($validated['status'])
            && $validated['status'] !== 'LOST'
        ) {
            $validated['lost_reason'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $lead->update($validated);

        $lead->load([
            'software:id,name,slug',
            'user:id,name,email',
            'assignedAdmin:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully.',
            'data' => $lead,
        ]);
    }

    /**
     * PATCH /api/v1/admin/leads/{lead}/assign
     *
     * Assign lead to admin.
     */
    public function assign(
        Request $request,
        ImplementationRequest $lead
    ): JsonResponse {
        $validated = $request->validate([
            'assigned_to' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ]);

        $admin = User::find($validated['assigned_to']);

        if (!$admin->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Selected user does not have admin role.',
            ], 422);
        }

        $lead->assigned_to = $admin->id;

        if ($lead->status === 'NEW') {
            $lead->status = 'ASSIGNED';
        }

        $lead->save();

        $lead->load([
            'software:id,name,slug',
            'user:id,name,email',
            'assignedAdmin:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead assigned successfully.',
            'data' => $lead,
        ]);
    }

    /**
     * PATCH /api/v1/admin/leads/{lead}/status
     *
     * Update lead status.
     */
    public function updateStatus(
        Request $request,
        ImplementationRequest $lead
    ): JsonResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(self::STATUSES),
            ],

            'lost_reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $status = $validated['status'];

        /*
        |--------------------------------------------------------------------------
        | LOST requires reason
        |--------------------------------------------------------------------------
        */

        if (
            $status === 'LOST'
            && empty($validated['lost_reason'])
            && empty($lead->lost_reason)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Lost reason is required when lead status is LOST.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Update Status
        |--------------------------------------------------------------------------
        */

        $lead->status = $status;

        switch ($status) {
            case 'QUALIFIED':
                $lead->qualified_at =
                    $lead->qualified_at ?? now();
                break;

            case 'PROPOSAL_SENT':
                $lead->proposal_sent_at =
                    $lead->proposal_sent_at ?? now();
                break;

            case 'WON':
                $lead->closed_at =
                    $lead->closed_at ?? now();

                $lead->lost_reason = null;
                break;

            case 'LOST':
                $lead->closed_at =
                    $lead->closed_at ?? now();

                $lead->lost_reason =
                    $validated['lost_reason'];
                break;

            default:
                if ($status !== 'LOST') {
                    $lead->lost_reason = null;
                }
                break;
        }

        $lead->save();

        $lead->load([
            'software:id,name,slug',
            'user:id,name,email',
            'assignedAdmin:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead status updated successfully.',
            'data' => $lead,
        ]);
    }

    /**
     * DELETE /api/v1/admin/leads/{lead}
     *
     * Delete lead.
     */
    public function destroy(
        ImplementationRequest $lead
    ): JsonResponse {
        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead deleted successfully.',
        ]);
    }
}