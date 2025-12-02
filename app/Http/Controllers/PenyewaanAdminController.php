<?php

namespace App\Http\Controllers;

use App\Models\Penyewaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PengingatBayarNotification;
use App\Notifications\SewaDibatalkanNotification;
use Carbon\Carbon;

class PenyewaanAdminController extends Controller
{
    // POST /api/stands/{stand}/kirim-pengingat
    public function kirimPengingat($standId)
    {
        // Hanya admin (pastikan route pakai middleware role:admin)
        // Cari penyewaan yang masih menunggu pembayaran di stand ini
        $penyewaan = Penyewaan::with(['user', 'stand'])
            ->where('stand_id', $standId)
            ->where('status_sewa', 'menunggu pembayaran')
            ->where('status_pembayaran', 'menunggu pembayaran')
            ->first();

        if (! $penyewaan) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada penyewaan yang menunggu pembayaran untuk stand ini.',
            ], 404);
        }

        // Batas 7 hari sejak tanggal_mulai_sewa
        $today      = Carbon::today();
        $mulai      = Carbon::parse($penyewaan->tanggal_mulai_sewa);
        $batasAkhir = $mulai->copy()->addDays(7);

        if ($today->lt($mulai) || $today->gt($batasAkhir)) {
            return response()->json([
                'success' => false,
                'message' => 'Pengingat hanya boleh dikirim dalam 7 hari sejak tanggal mulai sewa.',
            ], 422);
        }

        // Hanya boleh 1x per hari
        if ($penyewaan->last_notified_at &&
            $penyewaan->last_notified_at->isSameDay(Carbon::now())) {

            return response()->json([
                'success' => false,
                'message' => 'Hari ini sudah dikirim pengingat pembayaran untuk penyewaan ini.',
            ], 422);
        }

        // Update waktu pengingat
        $penyewaan->last_notified_at = Carbon::now();
        $penyewaan->save();

        // Kirim notifikasi ke penyewa (email + database)
        $penyewaan->user->notify(new PengingatBayarNotification($penyewaan));

        return response()->json([
            'success' => true,
            'message' => 'Pengingat pembayaran berhasil dikirim ke penyewa.',
        ], 200);
    }

    // POST /api/stands/{stand}/batalkan-sewa
    public function batalkanSewa(Request $request, $standId)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $penyewaan = Penyewaan::with(['user', 'stand'])
            ->where('stand_id', $standId)
            ->where('status_sewa', 'menunggu pembayaran')
            ->where('status_pembayaran', 'menunggu pembayaran')
            ->first();

        if (! $penyewaan) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada penyewaan yang bisa dibatalkan untuk stand ini.',
            ], 404);
        }

        // Update status & alasan pembatalan
        $penyewaan->status_sewa        = 'dibatalkan';
        $penyewaan->status_pembayaran  = 'gagal'; // opsional, supaya jelas tidak jadi bayar
        $penyewaan->alasan_pembatalan  = $request->alasan;
        $penyewaan->save();

        // Ubah status stand jadi kosong lagi
        $penyewaan->stand->update([
            'status_stand' => 'kosong',
        ]);

        // Kirim notifikasi pembatalan ke penyewa
        $penyewaan->user->notify(new SewaDibatalkanNotification($penyewaan));

        return response()->json([
            'success' => true,
            'message' => 'Sewa berhasil dibatalkan dan penyewa telah diberitahu.',
        ], 200);
    }
}
