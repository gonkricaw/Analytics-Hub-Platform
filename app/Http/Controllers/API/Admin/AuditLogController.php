<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('audit-log-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view audit logs'
                ], 403);
            }

            $query = AuditLog::with('user')->latest();

            // Filter by user ID
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Filter by action
            if ($request->has('action')) {
                $query->where('action', $request->action);
            }

            // Filter by model type
            if ($request->has('model_type')) {
                $query->where('model_type', $request->model_type);
            }

            // Filter by date range
            if ($request->has('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }

            if ($request->has('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            // Paginate the results
            $perPage = $request->get('per_page', 15);
            $logs = $query->paginate($perPage);

            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve audit logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified audit log.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('audit-log-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view audit logs'
                ], 403);
            }

            $log = AuditLog::with('user')->findOrFail($id);

            return response()->json($log);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve audit log',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get audit log actions for dropdown filtering
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActions()
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('audit-log-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view audit logs'
                ], 403);
            }

            $actions = AuditLog::select('action')
                ->distinct()
                ->orderBy('action')
                ->get()
                ->pluck('action');

            return response()->json($actions);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve audit log actions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get model types for dropdown filtering
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getModelTypes()
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('audit-log-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view audit logs'
                ], 403);
            }

            $modelTypes = AuditLog::select('model_type')
                ->whereNotNull('model_type')
                ->distinct()
                ->orderBy('model_type')
                ->get()
                ->pluck('model_type')
                ->map(function ($type) {
                    // Extract class name from full namespace
                    $parts = explode('\\', $type);
                    return end($parts);
                });

            return response()->json($modelTypes);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve model types',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
