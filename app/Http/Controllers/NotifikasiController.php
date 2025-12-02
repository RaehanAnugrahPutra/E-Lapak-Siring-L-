<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Notifikasi;
use Carbon\Carbon;
use App\Notifications\PengingatBayarNotification;
use App\Notifications\SewaDibatalkanNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;

class NotifikasiController extends Controller
{
    // GET /api/notifikasi
    // Ambil semua notifikasi milik user yang login (penyewa / admin)
    public function index(Request $request)
    {
        $user = $request->user(); // user dari token sanctum

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications->count(),
            'data' => $user->notifications, // kalau mau hanya unread, pakai unreadNotifications
        ]);
    }

    // POST /api/notifikasi/{id}/read
    // Tandai satu notifikasi sebagai sudah dibaca
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();

        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi telah ditandai sebagai dibaca.',
        ]);
    }

    // (opsional) POST /api/notifikasi/read-all
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi telah ditandai sebagai dibaca.',
        ]);
    }
}
