<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            $query = SystemNotification::forUser($user)
                                     ->active()
                                     ->orderBy('created_at', 'desc');

            // Filter by read status if specified
            if ($request->has('is_read')) {
                $query->where('is_read', filter_var($request->is_read, FILTER_VALIDATE_BOOLEAN));
            }

            // Paginate results
            $perPage = $request->get('per_page', 15);
            $notifications = $query->paginate($perPage);

            return response()->json($notifications, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('notification-create')) {
                return response()->json([
                    'message' => 'You do not have permission to create notifications'
                ], 403);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'type' => 'required|string|in:info,success,warning,error',
                'is_global' => 'boolean',
                'user_id' => 'required_unless:is_global,true|nullable|exists:users,id',
                'expires_at' => 'nullable|date',
                'link' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create notification
            $notification = SystemNotification::create([
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'user_id' => $request->has('user_id') ? $request->user_id : null,
                'is_global' => $request->is_global ?? false,
                'is_read' => false,
                'expires_at' => $request->expires_at,
                'link' => $request->link
            ]);

            return response()->json([
                'message' => 'Notification created successfully',
                'notification' => $notification
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = auth()->user();
            $notification = SystemNotification::forUser($user)->findOrFail($id);

            return response()->json([
                'notification' => $notification
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('notification-update')) {
                return response()->json([
                    'message' => 'You do not have permission to update notifications'
                ], 403);
            }

            $notification = SystemNotification::findOrFail($id);

            // Validate input
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'message' => 'sometimes|required|string',
                'type' => 'sometimes|required|string|in:info,success,warning,error',
                'is_global' => 'boolean',
                'user_id' => 'required_unless:is_global,true|nullable|exists:users,id',
                'expires_at' => 'nullable|date',
                'link' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update notification
            $notification->update($request->only([
                'title', 'message', 'type', 'user_id', 'is_global', 'expires_at', 'link'
            ]));

            return response()->json([
                'message' => 'Notification updated successfully',
                'notification' => $notification->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('notification-delete')) {
                return response()->json([
                    'message' => 'You do not have permission to delete notifications'
                ], 403);
            }

            $notification = SystemNotification::findOrFail($id);
            $notification->delete();

            return response()->json([
                'message' => 'Notification deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a notification as read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id)
    {
        try {
            $user = auth()->user();
            $notification = SystemNotification::forUser($user)->findOrFail($id);
            $notification->markAsRead();

            return response()->json([
                'message' => 'Notification marked as read'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark notification as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications for the authenticated user as read.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        try {
            $user = auth()->user();

            SystemNotification::forUser($user)
                            ->unread()
                            ->update([
                                'is_read' => true,
                                'read_at' => now()
                            ]);

            return response()->json([
                'message' => 'All notifications marked as read'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark all notifications as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notification count for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadCount()
    {
        try {
            $user = auth()->user();
            $count = SystemNotification::forUser($user)
                                     ->unread()
                                     ->active()
                                     ->count();

            return response()->json([
                'count' => $count
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve unread notification count',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
