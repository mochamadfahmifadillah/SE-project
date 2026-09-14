<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Get admin settings.
     */
    public function index(): JsonResponse
    {
        $settings = Setting::pluck('value', 'key');

        return response()->json([
            'success' => true,
            'message' => 'Settings retrieved successfully.',
            'data' => [
                'platform_name' => $settings['platform_name'] ?? 'Software Empire',
                'platform_url' => $settings['platform_url'] ?? '',
                'platform_email' => $settings['platform_email'] ?? '',
                'timezone' => $settings['timezone'] ?? 'Asia/Jakarta',

                'maintenance_mode' =>
                    filter_var(
                        $settings['maintenance_mode'] ?? false,
                        FILTER_VALIDATE_BOOLEAN
                    ),

                'email_notifications' =>
                    filter_var(
                        $settings['email_notifications'] ?? true,
                        FILTER_VALIDATE_BOOLEAN
                    ),

                'review_notifications' =>
                    filter_var(
                        $settings['review_notifications'] ?? true,
                        FILTER_VALIDATE_BOOLEAN
                    ),

                'lead_notifications' =>
                    filter_var(
                        $settings['lead_notifications'] ?? true,
                        FILTER_VALIDATE_BOOLEAN
                    ),
            ],
        ]);
    }

    /**
     * Update admin settings.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform_name' => [
                'required',
                'string',
                'max:255',
            ],

            'platform_url' => [
                'nullable',
                'url',
                'max:255',
            ],

            'platform_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'timezone' => [
                'required',
                'string',
                'max:100',
            ],

            'maintenance_mode' => [
                'boolean',
            ],

            'email_notifications' => [
                'boolean',
            ],

            'review_notifications' => [
                'boolean',
            ],

            'lead_notifications' => [
                'boolean',
            ],
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : $value]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully.',
            'data' => $validated,
        ]);
    }
}