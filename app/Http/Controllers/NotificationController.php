<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::with(['user:id,name,email']);

        // Users can only see their own notifications
        $query->where('user_id', $request->user()->id);

        // Filter by read status
        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Only show active (non-expired) notifications
        $query->active();

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $notifications = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'notifications' => $notifications
            ]
        ]);
    }

    /**
     * Store a newly created notification.
     */
    public function store(StoreNotificationRequest $request): JsonResponse
    {
        // Only admins, owners, and members can create notifications
        if (!in_array($request->user()->user_role, ['admin', 'owner', 'member'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to create notifications'
            ], 403);
        }

        try {
            $notification = Notification::create($request->all());
            $notification->load(['user:id,name,email']);

            return response()->json([
                'status' => 'success',
                'message' => 'Notification created successfully',
                'data' => $notification
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified notification.
     */
    public function show(Notification $notification): JsonResponse
    {
        // Check if user can view this notification
        if ($notification->user_id !== request()->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $notification->load(['user:id,name,email']);

        return response()->json([
            'status' => 'success',
            'data' => $notification
        ]);
    }

    /**
     * Update the specified notification.
     */
    public function update(UpdateNotificationRequest $request, Notification $notification): JsonResponse
    {
        // Check if user can update this notification
        if ($notification->user_id !== $request->user()->id &&
            !in_array($request->user()->user_role, ['admin', 'owner'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $notification->update($request->only(['is_read']));

            return response()->json([
                'status' => 'success',
                'message' => 'Notification updated successfully',
                'data' => $notification
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified notification.
     */
    public function destroy(Notification $notification): JsonResponse
    {
        // Check if user can delete this notification
        if ($notification->user_id !== request()->user()->id &&
            !in_array(request()->user()->user_role, ['admin', 'owner'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $notification->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        // Check if user can update this notification
        if ($notification->user_id !== request()->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $notification->markAsRead();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark notification as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(Notification $notification): JsonResponse
    {
        // Check if user can update this notification
        if ($notification->user_id !== request()->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $notification->markAsUnread();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification marked as unread'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark notification as unread',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            Notification::where('user_id', $request->user()->id)
                       ->unread()
                       ->update(['is_read' => true]);

            return response()->json([
                'status' => 'success',
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark all notifications as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification statistics for the authenticated user.
     */
    public function statistics(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $stats = [
            'total_notifications' => Notification::where('user_id', $userId)->count(),
            'unread_notifications' => Notification::where('user_id', $userId)->unread()->count(),
            'read_notifications' => Notification::where('user_id', $userId)->read()->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}
