<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class AuthController extends Controller
{
    /**
     * Configure the legacy Cloudinary PHP SDK (v1.20.2).
     *
     * This SDK uses the global Cloudinary class, not
     * Cloudinary\Cloudinary.
     */
    private function configureCloudinary(): void
    {
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        if (
            !is_string($cloudName) || trim($cloudName) === '' ||
            !is_string($apiKey) || trim($apiKey) === '' ||
            !is_string($apiSecret) || trim($apiSecret) === ''
        ) {
            throw new RuntimeException(
                'Cloudinary credentials are missing or invalid.'
            );
        }

        if (
            !class_exists(\Cloudinary::class) ||
            !class_exists(\Cloudinary\Uploader::class)
        ) {
            throw new RuntimeException(
                'The installed Cloudinary PHP SDK is not available.'
            );
        }

        \Cloudinary::config([
            'cloud_name' => $cloudName,
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'secure' => true,
        ]);
    }

    /**
     * POST /api/v1/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $userRole = Role::where('name', 'user')->first();

        if ($userRole) {
            $user->roles()->syncWithoutDetaching([$userRole->id]);
        }

        $user->load(['roles.permissions']);

        $token = $user
            ->createToken('software-empire')
            ->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * POST /api/v1/login
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', $validated['email'])
            ->first();

        if (
            !$user ||
            !Hash::check($validated['password'], $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    'The provided credentials are incorrect.',
                ],
            ]);
        }

        // Preserve existing behavior: invalidate previous tokens.
        $user->tokens()->delete();

        $user->load(['roles.permissions']);

        $token = $user
            ->createToken('software-empire')
            ->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    /**
     * POST /api/v1/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()
            ?->currentAccessToken()
            ?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * GET /api/v1/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->load(['roles.permissions']);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    /**
     * PATCH /api/v1/me
     *
     * Update the authenticated user's profile.
     * New avatars are uploaded to Cloudinary.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'business_size' => ['nullable', 'string', 'max:100'],
            'industry' => ['nullable', 'string', 'max:255'],
            'business_need' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:2048',
            ],
        ]);

        // Never pass an UploadedFile to User::fill().
        unset($validated['avatar']);

        $oldAvatar = $user->avatar;
        $newAvatarUrl = null;
        $newAvatarPublicId = null;

        /*
         * Upload the avatar before updating the database.
         */
        if ($request->hasFile('avatar')) {
            try {
                $file = $request->file('avatar');

                if (!$file || !$file->isValid()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The uploaded avatar is invalid.',
                    ], 422);
                }

                $this->configureCloudinary();

                /*
                 * Cloudinary PHP SDK 1.20.2 uses the legacy static API.
                 */
                $uploadResult = \Cloudinary\Uploader::upload(
                    $file->getRealPath(),
                    [
                        'folder' => 'software-empire/avatars',
                        'resource_type' => 'image',
                        'use_filename' => false,
                        'unique_filename' => true,
                    ]
                );

                if (!is_array($uploadResult)) {
                    throw new RuntimeException(
                        'Cloudinary returned an unexpected upload response.'
                    );
                }

                $newAvatarUrl = $uploadResult['secure_url'] ?? null;
                $newAvatarPublicId = $uploadResult['public_id'] ?? null;

                if (
                    !is_string($newAvatarUrl) ||
                    filter_var($newAvatarUrl, FILTER_VALIDATE_URL) === false ||
                    parse_url($newAvatarUrl, PHP_URL_SCHEME) !== 'https' ||
                    !is_string($newAvatarPublicId) ||
                    trim($newAvatarPublicId) === ''
                ) {
                    throw new RuntimeException(
                        'Cloudinary returned an invalid secure URL or public ID.'
                    );
                }
            } catch (Throwable $exception) {
                Log::error('Cloudinary avatar upload failed.', [
                    'user_id' => $user->id,
                    'exception_class' => get_class($exception),
                    'error' => $exception->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload avatar. Please try again.',
                ], 500);
            }
        }

        /*
         * Save the profile and, if uploaded, the HTTPS Cloudinary URL.
         */
        try {
            if ($newAvatarUrl !== null) {
                $validated['avatar'] = $newAvatarUrl;
            }

            $user->fill($validated);
            $user->save();
        } catch (Throwable $exception) {
            /*
             * The upload succeeded but the database update failed.
             * Try to remove the unused Cloudinary image.
             */
            if (
                $newAvatarPublicId !== null &&
                class_exists(\Cloudinary\Uploader::class) &&
                method_exists(\Cloudinary\Uploader::class, 'destroy')
            ) {
                try {
                    $this->configureCloudinary();

                    \Cloudinary\Uploader::destroy(
                        $newAvatarPublicId,
                        ['resource_type' => 'image']
                    );
                } catch (Throwable $cleanupException) {
                    Log::warning(
                        'Failed to clean up an unused Cloudinary avatar.',
                        [
                            'user_id' => $user->id,
                            'public_id' => $newAvatarPublicId,
                            'error' => $cleanupException->getMessage(),
                        ]
                    );
                }
            }

            Log::error('Profile database update failed.', [
                'user_id' => $user->id,
                'exception_class' => get_class($exception),
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile. Please try again.',
            ], 500);
        }

        /*
         * Remove an old local avatar only after saving succeeds.
         * Do not pass URLs to the local Storage disk.
         */
        if (
            $newAvatarUrl !== null &&
            is_string($oldAvatar) &&
            trim($oldAvatar) !== '' &&
            !preg_match('~^https?://~i', $oldAvatar)
        ) {
            try {
                $oldPath = ltrim($oldAvatar, '/');

                if (str_starts_with($oldPath, 'storage/')) {
                    $oldPath = substr($oldPath, strlen('storage/'));
                }

                if (
                    $oldPath !== '' &&
                    !str_contains($oldPath, '..') &&
                    Storage::disk('public')->exists($oldPath)
                ) {
                    Storage::disk('public')->delete($oldPath);
                }
            } catch (Throwable $exception) {
                Log::warning('Failed to delete the previous local avatar.', [
                    'user_id' => $user->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $user->refresh();
        $user->load(['roles.permissions']);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => [
                'user' => $user,
            ],
        ]);
    }
}