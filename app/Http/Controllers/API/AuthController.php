<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Login user and create token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(\App\Http\Requests\LoginRequest $request)
    {
        try {

            // Check if IP is blocked due to too many attempts
            $ipAddress = $request->ip();
            $email = $request->email;

            $loginAttempt = LoginAttempt::firstOrNew([
                'email' => $email,
                'ip_address' => $ipAddress
            ]);

            // Check if IP is blocked
            if ($loginAttempt->exists && $loginAttempt->isBlocked()) {
                return response()->json([
                    'message' => 'Too many failed login attempts. Please try again later.',
                    'blocked_until' => $loginAttempt->blocked_until
                ], 429);
            }

            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                // Increment login attempts for this IP and email
                if ($loginAttempt->exists) {
                    $loginAttempt->incrementAttempts();
                } else {
                    $loginAttempt->fill(['attempts' => 1, 'last_attempt_at' => now()])->save();
                }

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

            // Reset login attempts for this IP and email combination
            if ($loginAttempt->exists) {
                $loginAttempt->resetAttempts();
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
     * @param \App\Http\Requests\RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(\App\Http\Requests\RegisterRequest $request)
    {
        try {

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
    public function changePassword(\App\Http\Requests\ChangePasswordRequest $request)
    {
        try {

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
     * Change password for first time users (e.g. after invitation)
     * Unlike regular changePassword, this doesn't require the current password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePasswordFirstTime(\App\Http\Requests\ChangePasswordFirstTimeRequest $request)
    {
        try {

            $user = $request->user();

            // Check if the user is required to change their password
            if (!$user->force_password_change) {
                return response()->json([
                    'message' => 'Password change not required. Use the standard change password endpoint.'
                ], 400);
            }

            // Update password
            $user->password = Hash::make($request->password);
            $user->force_password_change = false;
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
     * @param \App\Http\Requests\ForgotPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(\App\Http\Requests\ForgotPasswordRequest $request)
    {
        try {
            // Get user by email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                // We don't want to reveal if an email exists or not due to security concerns
                return response()->json([
                    'message' => 'Password reset link has been sent to your email if it exists in our system'
                ], 200);
            }

            // Generate reset token
            $token = Password::createToken($user);

            // Send email with reset link using EmailService
            try {
                $emailService = app(\App\Services\EmailService::class);
                $emailSent = $emailService->sendPasswordResetEmail($user, $token);

                if (!$emailSent) {
                    \Log::error('Failed to send password reset email through EmailService');

                    // Fallback to traditional mail sending
                    \Mail::to($user->email)->send(new \App\Mail\PasswordReset($user->email, $token));
                }
            } catch (\Exception $mailException) {
                \Log::error('Failed to send password reset email: ' . $mailException->getMessage());

                // Return generic message to avoid revealing email sending errors
                return response()->json([
                    'message' => 'Error sending email, please try again later'
                ], 500);
            }

            return response()->json([
                'message' => 'Password reset link has been sent to your email'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Password reset error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to process your request',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Reset user password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(\App\Http\Requests\ResetPasswordRequest $request)
    {
        try {

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'force_password_change' => false,
                        'remember_token' => Str::random(60),
                    ])->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Password has been successfully reset'
                ], 200);
            }

            return response()->json([
                'message' => 'Invalid or expired token',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reset password',
                'error' => $e->getMessage()
            ], 500);
        }
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

            $now = now();

            // Record acceptance
            $user->acceptedTerms()->attach($termId, [
                'accepted_at' => $now,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Get the terms and send confirmation email
            $terms = \App\Models\TermAndCondition::find($termId);
            $emailService = app(\App\Services\EmailService::class);
            $emailService->sendTermsAcceptanceEmail($user, $terms, $now->format('Y-m-d H:i:s'));

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
     * Get the authenticated User
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();

            // Load roles and permissions
            $user->load('roles.permissions');

            // Check if user needs to accept latest terms
            $needsToAcceptTerms = $this->needsToAcceptTerms($user);

            return response()->json([
                'user' => $user,
                'force_password_change' => $user->force_password_change,
                'needs_to_accept_terms' => $needsToAcceptTerms
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve user information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh the user's authentication token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        try {
            $user = $request->user();

            // Revoke the current token
            $request->user()->currentAccessToken()->delete();

            // Generate a new token
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'message' => 'Token refreshed successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to refresh token',
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
