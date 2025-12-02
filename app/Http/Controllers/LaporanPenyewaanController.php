<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stand;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Penyewaan;
use Carbon\Carbon;

class LaporanPenyewaanController extends Controller
{
    public function index()
    {
        $tahun = request('tahun');
        $bulan = request('bulan');

        // DEFAULT: pakai tahun & bulan sekarang jika tidak diisi dan bukan 'all'
        if ($bulan !== 'all') {
            $tahun = $tahun ?: Carbon::now()->year;
            $bulan = $bulan ?: Carbon::now()->month;

            $awalBulan  = Carbon::create($tahun, $bulan, 1)->startOfDay();
            $akhirBulan = (clone $awalBulan)->endOfMonth()->endOfDay();

            // METRIK KEUANGAN (berdasarkan WAKTU PEMBAYARAN)
            $totalPendapatan = Penyewaan::where('status_pembayaran', 'berhasil')
                ->whereBetween('waktu_pembayaran', [$awalBulan, $akhirBulan])
                ->sum('total_pembayaran');

            $totalTransaksiLunas = Penyewaan::where('status_pembayaran', 'berhasil')
                ->whereBetween('waktu_pembayaran', [$awalBulan, $akhirBulan])
                ->count();

            // ✅ FIX: METRIK STATUS SEWA - Hanya hitung yang aktif atau menunggu pembayaran
            $totalStandDisewaPeriode = Penyewaan::whereIn('status_sewa', ['aktif', 'menunggu pembayaran'])
                ->whereBetween('tanggal_mulai_sewa', [$awalBulan, $akhirBulan])
                ->count();

            $standBelumBayarPeriode = Penyewaan::where('status_sewa', 'menunggu pembayaran')
                ->where('status_pembayaran', 'menunggu pembayaran')
                ->whereBetween('tanggal_mulai_sewa', [$awalBulan, $akhirBulan])
                ->count();

            $totalBelumBayarPeriode = Penyewaan::where('status_sewa', 'menunggu pembayaran')
                ->where('status_pembayaran', 'menunggu pembayaran')
                ->whereBetween('tanggal_mulai_sewa', [$awalBulan, $akhirBulan])
                ->sum('total_pembayaran');

            $periodeInfo = [
                'mode'  => 'bulanan',
                'tahun' => (int) $tahun,
                'bulan' => (int) $bulan,
                'awal'  => $awalBulan,
                'akhir' => $akhirBulan,
                'label' => $awalBulan->translatedFormat('F Y'),
            ];
        } else {
            // MODE: SEMUA PERIODE (TANPA FILTER TANGGAL)
            $totalPendapatan = Penyewaan::where('status_pembayaran', 'berhasil')
                ->sum('total_pembayaran');

            $totalTransaksiLunas = Penyewaan::where('status_pembayaran', 'berhasil')
                ->count();

            // ✅ FIX: Hanya hitung yang aktif atau menunggu pembayaran
            $totalStandDisewaPeriode = Penyewaan::whereIn('status_sewa', ['aktif', 'menunggu pembayaran'])
                ->count();

            $standBelumBayarPeriode = Penyewaan::where('status_sewa', 'menunggu pembayaran')
                ->where('status_pembayaran', 'menunggu pembayaran')
                ->count();

            $totalBelumBayarPeriode = Penyewaan::where('status_sewa', 'menunggu pembayaran')
                ->where('status_pembayaran', 'menunggu pembayaran')
                ->sum('total_pembayaran');

            $periodeInfo = [
                'mode'  => 'all',
                'label' => 'Semua Periode',
            ];
        }

        // Transaksi terakhir (5 pembayaran berhasil terbaru)
        $transaksiTerakhir = Penyewaan::with(['stand', 'pengajuan', 'user'])
            ->where('status_pembayaran', 'berhasil')
            ->where('status_sewa', 'aktif') // ✅ TAMBAHAN: Hanya yang aktif
            ->orderBy('waktu_pembayaran', 'desc')
            ->take(5)
            ->get()
            ->map(function ($p) {
                $namaPenyewa = $p->pengajuan->nama_pengaju
                    ?? optional($p->user)->name
                    ?? 'Penyewa';

                return [
                    'nama_penyewa'     => $namaPenyewa,
                    'kode_stand'       => optional($p->stand)->kode_stand ?? '-',
                    'durasi_sewa_hari' => $p->durasi_sewa,
                    'total_pembayaran' => $p->total_pembayaran,
                    'waktu_pembayaran' => $p->waktu_pembayaran,
                ];
            });

        return response()->json([
            'success'  => true,
            'message'  => 'Laporan penyewaan',
            'periode'  => $periodeInfo,
            'ringkasan' => [
                'total_pendapatan'               => $totalPendapatan,
                'total_transaksi_lunas'          => $totalTransaksiLunas,
                'total_stand_disewa_periode'     => $totalStandDisewaPeriode,
                'stand_belum_bayar_periode'      => $standBelumBayarPeriode,
                'total_belum_bayar_periode'      => $totalBelumBayarPeriode,
            ],
            'transaksi_terakhir' => $transaksiTerakhir,
        ], 200);
    }
}
