<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalNotification;
use App\Services\ApprovalNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for Approval Notifications
 */
class ApprovalNotificationApiController extends Controller
{
    protected ApprovalNotificationService $notificationService;

    public function __construct(ApprovalNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user notifications
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $query = ApprovalNotification::where('recipient_id', $userId);

            // Apply filters
            if ($request->has('notification_type')) {
                $query->where('notification_type', $request->notification_type);
            }

            if ($request->has('read_status')) {
                if ($request->read_status === 'read') {
                    $query->whereNotNull('read_at');
                } else {
                    $query->whereNull('read_at');
                }
            }

            if ($request->has('date_from')) {
                $query->where('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('created_at', '<=', $request->date_to);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $notifications = $query->with(['documentApproval'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get notifications', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unread notifications
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unread(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $query = ApprovalNotification::where('recipient_id', $userId)
                ->whereNull('read_at');

            // Apply filters
            if ($request->has('notification_type')) {
                $query->where('notification_type', $request->notification_type);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $notifications = $query->with(['documentApproval'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get unread notifications', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve unread notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark notification as read
     *
     * @param int $id
     * @return JsonResponse
     */
    public function markAsRead(int $id): JsonResponse
    {
        try {
            $userId = auth()->id();
            $notification = ApprovalNotification::where('id', $id)
                ->where('recipient_id', $userId)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], 404);
            }

            $notification->read_at = now();
            $notification->save();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'data' => $notification,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     *
     * @return JsonResponse
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $count = ApprovalNotification::where('recipient_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'data' => [
                    'updated_count' => $count,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete notification
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $userId = auth()->id();
            $notification = ApprovalNotification::where('id', $id)
                ->where('recipient_id', $userId)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], 404);
            }

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete notification', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear old notifications
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clearOld(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $days = $request->get('days', 30);

            $count = ApprovalNotification::where('recipient_id', $userId)
                ->where('created_at', '<', now()->subDays($days))
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Old notifications cleared successfully',
                'data' => [
                    'deleted_count' => $count,
                    'days_old' => $days,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear old notifications', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear old notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
