<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAvatarRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Update the user's avatar
     *
     * @param UpdateAvatarRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAvatar(UpdateAvatarRequest $request)
    {
        try {
            $user = auth()->user();

            // Handle file upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if it exists
                if ($user->avatar && !Str::startsWith($user->avatar, 'default/')) {
                    Storage::disk('public')->delete($user->avatar);
                }

                // Store the new avatar
                $path = $request->file('avatar')->store('avatars/' . $user->id, 'public');

                // Update the user model
                $user->avatar = $path;
                $user->save();

                return response()->json([
                    'message' => 'Avatar updated successfully',
                    'user' => $user->fresh(),
                    'avatar_url' => Storage::url($path)
                ], 200);
            }

            return response()->json([
                'message' => 'No avatar file provided',
                'user' => $user
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update avatar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the user's avatar URL
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvatar()
    {
        try {
            $user = auth()->user();

            if ($user->avatar) {
                return response()->json([
                    'avatar_url' => Storage::url($user->avatar)
                ], 200);
            }

            return response()->json([
                'avatar_url' => '/images/default-avatar.png' // Default avatar path
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve avatar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the user's avatar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeAvatar()
    {
        try {
            $user = auth()->user();

            // Check if user has a custom avatar (not a default one)
            if ($user->avatar && !Str::startsWith($user->avatar, 'default/')) {
                // Delete the avatar file from storage
                Storage::disk('public')->delete($user->avatar);
            }

            // Reset to default avatar or null
            $user->avatar = null;
            $user->save();

            return response()->json([
                'message' => 'Avatar removed successfully',
                'user' => $user->fresh(),
                'avatar_url' => '/images/default-avatar.png'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove avatar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
