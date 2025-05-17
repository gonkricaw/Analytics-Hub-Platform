<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    /**
     * Display a listing of all blocked IPs
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBlockedIps()
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('security-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view security settings'
                ], 403);
            }

            // Get all blocked IPs
            $blockedIps = LoginAttempt::where('is_blocked', true)
                ->where('blocked_until', '>', now())
                ->get([
                    'id', 'email', 'ip_address', 'attempts',
                    'blocked_until', 'last_attempt_at', 'created_at', 'updated_at'
                ]);

            return response()->json([
                'blocked_ips' => $blockedIps
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve blocked IPs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unblock an IP address
     *
     * @param Request $request
     * @param int $id LoginAttempt ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function unblockIp(Request $request, $id)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('security-manage')) {
                return response()->json([
                    'message' => 'You do not have permission to manage security settings'
                ], 403);
            }

            $loginAttempt = LoginAttempt::findOrFail($id);

            // Reset the login attempt record
            $loginAttempt->update([
                'attempts' => 0,
                'is_blocked' => false,
                'blocked_until' => null
            ]);

            // Log this action
            if (class_exists('\App\Models\AuditLog')) {
                \App\Models\AuditLog::log(
                    'unblock_ip',
                    $loginAttempt,
                    ['is_blocked' => true],
                    ['is_blocked' => false]
                );
            }

            return response()->json([
                'message' => "IP address {$loginAttempt->ip_address} has been unblocked successfully",
                'login_attempt' => $loginAttempt
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to unblock IP address',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get login history for security monitoring
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLoginHistory(Request $request)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('security-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view security settings'
                ], 403);
            }

            $query = LoginAttempt::query();

            // Apply filters
            if ($request->has('email')) {
                $query->where('email', 'like', '%' . $request->email . '%');
            }

            if ($request->has('ip_address')) {
                $query->where('ip_address', $request->ip_address);
            }

            if ($request->has('status')) {
                if ($request->status === 'blocked') {
                    $query->where('is_blocked', true);
                } elseif ($request->status === 'failed') {
                    $query->where('attempts', '>', 0);
                }
            }

            // Order by most recent attempts
            $query->orderBy('last_attempt_at', 'desc');

            // Paginate results
            $perPage = $request->get('per_page', 15);
            $loginHistory = $query->paginate($perPage);

            return response()->json($loginHistory, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve login history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
