<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StandController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\PenyewaanController;
use App\Http\Controllers\LaporanPenyewaanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Models\User;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// auth routes
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/lupa-password', [App\Http\Controllers\AuthController::class, 'forgotPassword']);
Route::post('/ganti-password', [App\Http\Controllers\AuthController::class, 'resetPassword']);
Route::post('/verify-reset-token', [App\Http\Controllers\AuthController::class, 'verifyResetToken']);

//route stand
Route::get('/stands', [StandController::class, 'index']);
Route::get('/stands/{id}', [StandController::class, 'show']);
Route::post('/stands', [StandController::class, 'store'])->middleware('auth:sanctum','role:admin');
Route::put('/stands/{id}', [StandController::class, 'update'])->middleware('auth:sanctum','role:admin');
Route::delete('/stands/{id}', [StandController::class, 'destroy'])->middleware('auth:sanctum','role:admin');

//route pengajuan
Route::get('/pengajuan', [PengajuanController::class, 'index']);
Route::get('/pengajuan/{id}', [PengajuanController::class, 'show']);
Route::post('/pengajuan', [PengajuanController::class, 'store'])->middleware('auth:sanctum','role:penyewa');
Route::get('/pengajuan/{id}/surat', [PengajuanController::class, 'downloadSurat'])->middleware('auth:sanctum','role:admin');
Route::post('/pengajuan/{id}/approve', [PengajuanController::class, 'approve'])->middleware('auth:sanctum','role:admin');
Route::post('/pengajuan/{id}/reject', [PengajuanController::class, 'reject'])->middleware('auth:sanctum','role:admin');

//route penyewaan
Route::get('/penyewaan', [PenyewaanController::class, 'index']);
Route::get('/penyewaan/{id}', [PenyewaanController::class, 'show']);
Route::post('/penyewaan/{id}/metode-pembayaran', [PenyewaanController::class, 'pilihMetodePembayaran']);
Route::post('/penyewaan/bayar-va', [PenyewaanController::class, 'bayarDenganVa']);
Route::post('/penyewaan/bayar-qris', [PenyewaanController::class, 'bayarDenganQris']);

//route penyewaan admin notifikasi
Route::post('/stands/{stand}/kirim-pengingat', [App\Http\Controllers\PenyewaanAdminController::class, 'kirimPengingat'])->middleware('auth:sanctum','role:admin');
Route::post('/stands/{stand}/batalkan-sewa', [App\Http\Controllers\PenyewaanAdminController::class, 'batalkanSewa'])->middleware('auth:sanctum','role:admin');

//membaca notifikasi penyewa
Route::get('/notifikasi', [App\Http\Controllers\NotifikasiController::class, 'index'])->middleware('auth:sanctum', 'role:penyewa');
Route::post('/notifikasi/{id}/read', [App\Http\Controllers\NotifikasiController::class, 'markAsRead'])->middleware('auth:sanctum', 'role:penyewa');
Route::post('/notifikasi/read-all', [App\Http\Controllers\NotifikasiController::class, 'markAllAsRead'])->middleware('auth:sanctum', 'role:penyewa');

//route laporan penyewaan
Route::get('/laporan-penyewaan', [App\Http\Controllers\LaporanPenyewaanController::class, 'index'])->middleware('auth:sanctum','role:admin');

//kelola event
Route::get('/events', [App\Http\Controllers\EventController::class, 'index']);
Route::get('/events/{id}', [App\Http\Controllers\EventController::class, 'show']);
Route::post('/events', [App\Http\Controllers\EventController::class, 'store'])->middleware('auth:sanctum','role:admin');
Route::put('/events/{id}', [App\Http\Controllers\EventController::class, 'update'])->middleware('auth:sanctum','role:admin');
Route::delete('/events/{id}', [App\Http\Controllers\EventController::class, 'destroy'])->middleware('auth:sanctum','role:admin');
