<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and create token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $user = User::where('email', $request->email)->first();

            // Check if the user is active
            if (!$user->is_active) {
                return response()->json([
                    'message' => 'Your account is inactive. Please contact the administrator.'
                ], 403);
            }

            // Update login timestamp and IP
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip()
            ]);

            // Generate token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Load roles and permissions
            $user->load('roles.permissions');

            // Check if user needs to change password
            $forcePasswordChange = $user->force_password_change;

            // Check if user needs to accept terms
            $needsToAcceptTerms = $this->needsToAcceptTerms($user);

            return response()->json([
                'user' => $user,
                'token' => $token,
                'force_password_change' => $forcePasswordChange,
                'needs_to_accept_terms' => $needsToAcceptTerms
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user (Revoke the token)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }

    /**
     * Register a new user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'department' => 'nullable|string|max:255',
                'position' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'department' => $request->department,
                'position' => $request->position,
                'phone' => $request->phone,
                'force_password_change' => true, // Force new users to change password on first login
                'is_active' => true,
            ]);

            // Assign default role to the user (e.g., 'user')
            $defaultRole = Role::where('name', 'user')->first();
            if ($defaultRole) {
                $user->roles()->attach($defaultRole);
            }

            return response()->json([
                'message' => 'User successfully registered',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed|different:current_password',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            // Check if current password is correct
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            // Update password
            $user->password = Hash::make($request->password);
            $user->force_password_change = false; // Remove force password change flag
            $user->save();

            return response()->json([
                'message' => 'Password successfully changed'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Password change failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a reset password link to the user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // In a real implementation, you would send a password reset email here
            // For now, we'll just return a success message

            return response()->json([
                'message' => 'Password reset link has been sent to your email'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send password reset link',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset user password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        // This would be implemented in a real application with token verification
        // For now, just return a placeholder response
        return response()->json([
            'message' => 'Password reset endpoint not fully implemented'
        ], 501);
    }

    /**
     * Accept terms and conditions
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function acceptTerms(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'term_id' => 'required|exists:terms_and_conditions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $termId = $request->term_id;

            // Check if already accepted
            if ($user->acceptedTerms()->where('term_and_condition_id', $termId)->exists()) {
                return response()->json([
                    'message' => 'Terms already accepted'
                ], 200);
            }

            // Record acceptance
            $user->acceptedTerms()->attach($termId, [
                'accepted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'message' => 'Terms and conditions accepted'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to record terms acceptance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user needs to accept terms and conditions
     *
     * @param User $user
     * @return bool
     */
    private function needsToAcceptTerms(User $user)
    {
        // Get the latest active terms and conditions
        $latestTerms = \App\Models\TermAndCondition::where('is_active', true)
            ->orderBy('effective_date', 'desc')
            ->first();

        if (!$latestTerms) {
            return false; // No active terms exist
        }

        // Check if the user has accepted these terms
        return !$user->acceptedTerms()
            ->where('term_and_condition_id', $latestTerms->id)
            ->exists();
    }
}
