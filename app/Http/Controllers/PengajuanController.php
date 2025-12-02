<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengajuan;
use App\Models\Penyewaan;
use App\Models\Stand;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PengajuanController extends Controller
{
    // GET /api/pengajuan (opsional: bisa dibatasi hanya admin)
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'List Pengajuan',
            'data'    => Pengajuan::all(),
        ], 200);
    }

    // GET /api/pengajuan/{id}
    public function show($id)
    {
        $pengajuan = Pengajuan::find($id);

        if (! $pengajuan) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Pengajuan',
            'data'    => $pengajuan,
        ], 200);
    }

    // =========================
    // PENYEWA MENGAJUKAN SEWA
    // =========================
    // Route: POST /api/pengajuan
    // Middleware route: auth:sanctum, role:penyewa
   public function store(Request $request)
{
    // 1. Validasi input (TANPA user_id)
    $request->validate([
        'nama_pengaju'         => 'required|string|max:255',
        'no_hp'                => 'required|string|max:15',
        'tanggal_mulai_sewa'   => 'required|date',
        'tanggal_selesai_sewa' => 'required|date|after_or_equal:tanggal_mulai_sewa',
        'surat_pengajuan'      => 'required|file|mimes:doc,docx,pdf|max:2048',
        'status'               => 'in:pending,disetujui,ditolak',
        'stand_id'             => 'required|exists:stands,id',
    ]);

    // 2. Cek durasi minimal 30 hari
    $mulai   = Carbon::parse($request->tanggal_mulai_sewa);
    $selesai = Carbon::parse($request->tanggal_selesai_sewa);

    $selisihHari = $mulai->diffInDays($selesai);

    if ($selisihHari < 30) {
        return response()->json([
            'success' => false,
            'message' => 'Durasi sewa minimal 30 hari. Silakan pilih tanggal selesai minimal 30 hari dari tanggal mulai.',
        ], 422);
    }

    // 3. Cek status stand
    $stand = Stand::findOrFail($request->stand_id);

    if ($stand->status_stand !== 'kosong') {
        return response()->json([
            'success' => false,
            'message' => 'Stand sudah disewa, tidak bisa diajukan lagi.',
        ], 422);
    }

    // 4. Simpan surat pengajuan dengan nama file asli yang dirapikan
    $file = $request->file('surat_pengajuan');

    $originalName = $file->getClientOriginalName();             // contoh: Surat Pengajuan Rusdi.docx
    $namaBersih   = pathinfo($originalName, PATHINFO_FILENAME); // "Surat Pengajuan Rusdi"
    $namaBersih   = preg_replace('/[^A-Za-z0-9_\-]/', '_', $namaBersih); // ganti spasi & simbol jadi _
    $ext          = $file->getClientOriginalExtension();        // docx / pdf

    $namaFileFinal = time().'_'.$namaBersih.'.'.$ext;
    // hasil: 1732440000_Surat_Pengajuan_Rusdi.docx

    $suratPath = $file->storeAs('surat_pengajuan', $namaFileFinal, 'public');

    // 5. Simpan pengajuan, user_id diambil dari user login
    $pengajuan = Pengajuan::create([
        'nama_pengaju'         => $request->nama_pengaju,
        'no_hp'                => $request->no_hp,
        'tanggal_mulai_sewa'   => $request->tanggal_mulai_sewa,
        'tanggal_selesai_sewa' => $request->tanggal_selesai_sewa,
        'surat_pengajuan'      => $suratPath,          // surat_pengajuan/1732..._Surat_Pengajuan_Rusdi.docx
        'status'               => $request->status ?? 'pending',
        'user_id'              => $request->user()->id,
        'stand_id'             => $request->stand_id,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Pengajuan berhasil disimpan dan menunggu persetujuan admin.',
        'data'    => $pengajuan,
    ], 201);
}


    //lihat/download surat pengajuan
     public function downloadSurat($id)
{
    $pengajuan = Pengajuan::findOrFail($id);

    if (! $pengajuan->surat_pengajuan) {
        return response()->json([
            'success' => false,
            'message' => 'Surat pengajuan tidak ditemukan.',
        ], 404);
    }

    $path = $pengajuan->surat_pengajuan; // contoh: surat_pengajuan/1732..._Surat_Pengajuan_Rusdi.docx

    if (! Storage::disk('public')->exists($path)) {
        return response()->json([
            'success' => false,
            'message' => 'File surat tidak ada di storage.',
        ], 404);
    }

    // gunakan nama file yang tersimpan di path
    $downloadName = basename($path);

    return Storage::disk('public')->download($path, $downloadName);
}


    // =========================
    // ADMIN MENYETUJUI PENGAJUAN
    // =========================
    public function approve($id)
    {
        $pengajuan = Pengajuan::with('stand')->findOrFail($id);

        if ($pengajuan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan sudah diproses sebelumnya.',
            ], 400);
        }

        // Hitung durasi sewa dalam HARI
        $mulai   = Carbon::parse($pengajuan->tanggal_mulai_sewa);
        $selesai = Carbon::parse($pengajuan->tanggal_selesai_sewa);
        $durasiHari = $mulai->diffInDays($selesai);

        // Harga per 30 hari dari tabel stands
        $hargaPerBulan = $pengajuan->stand->harga_sewa;
        $hargaPerHari  = intdiv($hargaPerBulan, 30);
        $total         = $durasiHari * $hargaPerHari;

        $penyewaan = Penyewaan::create([
            'pengajuan_id'         => $pengajuan->id,
            'user_id'              => $pengajuan->user_id,
            'stand_id'             => $pengajuan->stand_id,
            'tanggal_mulai_sewa'   => $pengajuan->tanggal_mulai_sewa,
            'tanggal_selesai_sewa' => $pengajuan->tanggal_selesai_sewa,
            'harga_sewa'           => $hargaPerBulan,
            'durasi_sewa'          => $durasiHari,
            'total_pembayaran'     => $total,
            'status_sewa'          => 'menunggu pembayaran',
        ]);

        $pengajuan->status = 'disetujui';
        $pengajuan->save();

        $pengajuan->stand->update([
            'status_stand' => 'terisi',
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Pengajuan disetujui. Penyewaan dibuat dan penyewa sudah bisa melakukan pembayaran.',
            'pengajuan' => $pengajuan,
            'penyewaan' => $penyewaan,
        ], 200);
    }

    // =========================
    // ADMIN MENOLAK PENGAJUAN
    // =========================
    // Route: POST /api/pengajuan/{id}/reject
    // Middleware route: auth:sanctum, role:admin
    public function reject($id)
    {
        $pengajuan = Pengajuan::findOrFail($id);

        if ($pengajuan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan sudah diproses sebelumnya.',
            ], 400);
        }

        $pengajuan->status = 'ditolak';
        $pengajuan->save();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan telah ditolak.',
            'data'    => $pengajuan,
        ], 200);
    }
}
