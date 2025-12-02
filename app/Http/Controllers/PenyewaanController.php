<?php

namespace App\Http\Controllers;

use App\Models\Penyewaan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Stand;
use App\Models\Pengajuan;
use Illuminate\Support\Facades\Storage;

class PenyewaanController extends Controller
{
    // GET /api/penyewaan
    public function index()
    {
        $data = Penyewaan::with(['stand', 'pengajuan', 'user'])->get();

        return response()->json([
            'success' => true,
            'message' => 'List Penyewaan',
            'data'    => $data,
        ], 200);
    }

    // GET /api/penyewaan/{id}
    public function show($id)
    {
        $penyewaan = Penyewaan::with(['stand', 'pengajuan', 'user'])->find($id);

        if (! $penyewaan) {
            return response()->json([
                'success' => false,
                'message' => 'Penyewaan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Penyewaan',
            'data'    => $penyewaan,
        ], 200);
    }

    // POST /api/penyewaan/{id}/metode-pembayaran
    public function pilihMetodePembayaran(Request $request, $id)
    {
        $request->validate([
            'metode_pembayaran' => 'required|in:qris,va',
        ]);

        $penyewaan = Penyewaan::findOrFail($id);

        if ($penyewaan->status_sewa !== 'menunggu pembayaran') {
            return response()->json([
                'success' => false,
                'message' => 'Penyewaan tidak dalam status menunggu pembayaran',
            ], 400);
        }

        $metode = $request->metode_pembayaran;

        if ($metode === 'va') {
            // VA dummy 8 digit
            $va = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
            $penyewaan->va_number    = $va;
            $penyewaan->qris_payload = null;
        } else {
            // QRIS dummy payload (string unik)
            $payload = 'QRIS-PENYEWAAN-' . $penyewaan->id . '-' . Str::random(8);
            $penyewaan->qris_payload = $payload;
            $penyewaan->va_number    = null;
        }

        $penyewaan->metode_pembayaran = $metode;
        $penyewaan->status_pembayaran = 'menunggu pembayaran';
        $penyewaan->save();

        return response()->json([
            'success' => true,
            'message' => 'Metode pembayaran berhasil dipilih.',
            'data'    => $penyewaan,
        ], 200);
    }

    // POST /api/penyewaan/bayar-va
    public function bayarDenganVa(Request $request)
    {
        $request->validate([
            'va_number' => 'required|string|size:8',
        ]);

        $penyewaan = Penyewaan::where('va_number', $request->va_number)
            ->where('status_pembayaran', 'menunggu pembayaran')
            ->first();

        if (! $penyewaan) {
            return response()->json([
                'success' => false,
                'message' => 'VA tidak ditemukan atau sudah digunakan',
            ], 404);
        }

        $penyewaan->status_pembayaran = 'berhasil';
        $penyewaan->status_sewa       = 'aktif';
        $penyewaan->waktu_pembayaran  = now();
        $penyewaan->save();

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran VA berhasil, sewa sudah aktif.',
            'data'    => $penyewaan,
        ], 200);
    }

    // POST /api/penyewaan/bayar-qris
    public function bayarDenganQris(Request $request)
    {
        $request->validate([
            'penyewaan_id' => 'required|integer',
        ]);

        $penyewaan = Penyewaan::findOrFail($request->penyewaan_id);

        if ($penyewaan->metode_pembayaran !== 'qris') {
            return response()->json([
                'success' => false,
                'message' => 'Metode pembayaran untuk penyewaan ini bukan QRIS',
            ], 400);
        }

        if ($penyewaan->status_pembayaran !== 'menunggu pembayaran') {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran sudah diproses sebelumnya',
            ], 400);
        }

        $penyewaan->status_pembayaran = 'berhasil';
        $penyewaan->status_sewa       = 'aktif';
        $penyewaan->waktu_pembayaran  = now();
        $penyewaan->save();

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran QRIS berhasil, sewa sudah aktif.',
            'data'    => $penyewaan,
        ], 200);
    }
}
