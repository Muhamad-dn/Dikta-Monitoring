<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Tandai notifikasi dibaca dan langsung arahkan ke data perangkat di stok HB JKT / HB CKR.
     */
    public function go(AdminNotification $notification)
    {
        $notification->markAsRead();

        return redirect()->to($notification->url);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function markRead(Request $request, AdminNotification $notification)
    {
        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllRead(Request $request)
    {
        AdminNotification::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi berhasil ditandai sudah dibaca.');
    }

    /**
     * API Polling untuk pembaruan unread count dan notifikasi terbaru tanpa reload halaman.
     */
    public function poll(Request $request)
    {
        $unreadCount = AdminNotification::unread()->count();
        $notifications = AdminNotification::recent()
            ->take(12)
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->title,
                    'message' => $notif->message,
                    'device_name' => $notif->device_name,
                    'registration_number' => $notif->registration_number,
                    'hub' => $notif->hub,
                    'hub_label' => $notif->hub_label,
                    'hub_badge_class' => $notif->hub_badge_class,
                    'source' => $notif->source,
                    'is_read' => (bool)$notif->is_read,
                    'time_ago' => $notif->created_at ? $notif->created_at->diffForHumans() : '',
                    'go_url' => route('admin.notifications.go', $notif),
                ];
            });

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }
}
