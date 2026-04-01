<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // GET /api/notifications
    public function index(Request $request)
    {
        $query = Notification::where('user_id', Auth::id())->latest();

        if ($request->filled('type'))    $query->where('type', $request->type);
        if ($request->filled('is_read')) $query->where('is_read', (bool) $request->is_read);

        $perPage       = (int) $request->get('per_page', 15);
        $notifications = $query->paginate($perPage);
        $unreadCount   = Notification::where('user_id', Auth::id())->unread()->count();

        return response()->json([
            'status'       => 'success',
            'unread_count' => $unreadCount,
            'meta'         => [
                'total'        => $notifications->total(),
                'per_page'     => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'has_more'     => $notifications->hasMorePages(),
            ],
            'data' => NotificationResource::collection($notifications->items()),
        ]);
    }

    // GET /api/notifications/{id}
    // Otomatis tandai sudah dibaca saat detail dibuka
    public function show($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return response()->json([
            'status' => 'success',
            'data'   => new NotificationResource($notification),
        ]);
    }

    // PATCH /api/notifications/{id}/read
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->update(['is_read' => true]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Notifikasi ditandai sudah dibaca',
        ]);
    }

    // PATCH /api/notifications/read-all
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Semua notifikasi ditandai sudah dibaca',
        ]);
    }

    // DELETE /api/notifications/{id}
    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Notifikasi dihapus',
        ]);
    }
}