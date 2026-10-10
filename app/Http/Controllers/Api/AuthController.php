<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/v1/register
     *
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        // Assign the default user role.
        $userRole = Role::where('name', 'user')->first();

        if ($userRole) {
            $user->roles()->syncWithoutDetaching([
                $userRole->id,
            ]);
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
     *
     * Authenticate a user and create an access token.
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

        // Invalidate previous access tokens.
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
     *
     * Revoke the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request
            ->user()
            ?->currentAccessToken()
            ?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * GET /api/v1/me
     *
     * Return the authenticated user with roles and permissions.
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
     * Update profile details and optionally upload a profile avatar.
     *
     * Avatar files are stored on Laravel's public disk.
     * The database stores a relative path, e.g. avatars/example.jpg.
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

        // Keep the current avatar unless a new file is uploaded.
        unset($validated['avatar']);

        $oldAvatar = $user->avatar;
        $newAvatarPath = null;

        if ($request->hasFile('avatar')) {
            // Store the new image first.
            $newAvatarPath = $request
                ->file('avatar')
                ->store('avatars', 'public');

            if (!$newAvatarPath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store the avatar image.',
                ], 500);
            }
        }

        try {
            // Persist the profile fields and the new avatar path.
            if ($newAvatarPath !== null) {
                $validated['avatar'] = $newAvatarPath;
            }

            $user->update($validated);
        } catch (\Throwable $exception) {
            // Clean up the newly stored file if the database update fails.
            if ($newAvatarPath !== null) {
                Storage::disk('public')->delete($newAvatarPath);
            }

            throw $exception;
        }

        // Delete the previous locally stored avatar after a successful update.
        if (
            $newAvatarPath !== null &&
            is_string($oldAvatar) &&
            $oldAvatar !== '' &&
            !preg_match('/^https?:\/\//i', $oldAvatar) &&
            Storage::disk('public')->exists($oldAvatar)
        ) {
            Storage::disk('public')->delete($oldAvatar);
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
