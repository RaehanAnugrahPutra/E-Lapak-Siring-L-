<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penyewaan extends Model
{
    //
    protected $table = 'penyewaans';
    protected $fillable = [
        'tanggal_mulai_sewa',
        'tanggal_selesai_sewa',
        'harga_sewa',
        'durasi_sewa',
        'total_pembayaran',
        'status_sewa',
        'metode_pembayaran',
        'va_number',
        'qris_payload',
        'status_pembayaran',
        'waktu_pembayaran',
        'pengajuan_id',
        'user_id',
        'stand_id',
        'alasan_pembatalan',
        'last_notified_at',
    ];
    protected $hidden = [
        'updated_at',
    ];

    protected $casts = [
        'waktu_pembayaran' => 'datetime',
        'tanggal_mulai_sewa' => 'date',
        'tanggal_selesai_sewa' => 'date',
        'last_notified_at' => 'datetime',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function stand()
    {
        return $this->belongsTo(Stand::class);
    }
}
