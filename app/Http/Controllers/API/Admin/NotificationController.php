<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Jobs\DistributeSystemNotificationJob;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = SystemNotification::with('user');

            // Filter by type if provided
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Filter by global status if provided
            if ($request->has('is_global')) {
                $query->where('is_global', filter_var($request->is_global, FILTER_VALIDATE_BOOLEAN));
            }

            // Filter by active status (not expired) if requested
            if ($request->has('active') && filter_var($request->active, FILTER_VALIDATE_BOOLEAN)) {
                $query->active();
            }

            // Filter by date range if provided
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            }

            // Search by title or message
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('message', 'like', "%{$search}%");
                });
            }

            // Sort by specified column or default to created_at
            $sortColumn = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortColumn, $sortDirection);

            // Paginate results
            $perPage = $request->get('per_page', 15);
            $notifications = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $notifications
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created notification in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'type' => 'required|string|in:info,success,warning,error',
                'is_global' => 'boolean',
                'user_ids' => 'array|required_unless:is_global,true',
                'user_ids.*' => 'exists:users,id',
                'expires_at' => 'nullable|date',
                'link' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $isGlobal = $request->has('is_global') ? filter_var($request->is_global, FILTER_VALIDATE_BOOLEAN) : false;

            // Create the system notification
            $notification = SystemNotification::create([
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'is_global' => $isGlobal,
                'expires_at' => $request->expires_at,
                'link' => $request->link,
                'user_id' => auth()->id() // Creator of the notification
            ]);

            // Dispatch job to distribute notifications to users
            if ($isGlobal) {
                // For global notifications, pass null to distribute to all active users
                DistributeSystemNotificationJob::dispatch($notification, null);
            }
            // Otherwise, distribute only to specified users
            else if ($request->has('user_ids') && is_array($request->user_ids)) {
                DistributeSystemNotificationJob::dispatch($notification, $request->user_ids);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Notification created successfully',
                'data' => $notification
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified notification.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $notification = SystemNotification::with(['user'])->findOrFail($id);

            // Get all users who received this notification
            if ($notification->is_global) {
                $users = UserNotification::where('notification_id', $id)
                    ->with('user')
                    ->get()
                    ->pluck('user');

                $notification->recipients = $users;
            }

            return response()->json([
                'success' => true,
                'data' => $notification
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified notification in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $notification = SystemNotification::findOrFail($id);

            // Validate input
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'message' => 'sometimes|required|string',
                'type' => 'sometimes|required|string|in:info,success,warning,error',
                'expires_at' => 'nullable|date',
                'link' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update notification
            $notification->update($request->only([
                'title', 'message', 'type', 'expires_at', 'link'
            ]));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Notification updated successfully',
                'data' => $notification->fresh()
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified notification from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $notification = SystemNotification::findOrFail($id);

            // Delete all related user notifications first
            UserNotification::where('notification_id', $id)->delete();

            // Then delete the system notification
            $notification->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total' => SystemNotification::count(),
                'global' => SystemNotification::where('is_global', true)->count(),
                'by_type' => [
                    'info' => SystemNotification::where('type', 'info')->count(),
                    'success' => SystemNotification::where('type', 'success')->count(),
                    'warning' => SystemNotification::where('type', 'warning')->count(),
                    'error' => SystemNotification::where('type', 'error')->count(),
                ],
                'read_status' => [
                    'read' => UserNotification::where('is_read', true)->count(),
                    'unread' => UserNotification::where('is_read', false)->count(),
                ],
                'recent' => SystemNotification::latest()->take(5)->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notification statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
