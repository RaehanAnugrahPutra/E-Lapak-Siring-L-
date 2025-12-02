<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    //
    protected $table = 'pengajuans';
    protected $fillable = [
        'nama_pengaju',
        'no_hp',
        'tanggal_mulai_sewa',
        'tanggal_selesai_sewa',
        'surat_pengajuan',
        'status',
        'user_id',
        'stand_id',
    ];

    protected $hidden = [
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stand()
    {
        return $this->belongsTo(Stand::class);
    }

    public function penyewaan()
    {
        return $this->hasOne(Penyewaan::class);
    }
}
