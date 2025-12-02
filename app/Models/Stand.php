<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stand extends Model
{
    //
    protected $fillable = [
        'kode_stand',
        'status_stand',
    ];

    protected $table = 'stands';
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class);
    }

    public function penyewaans()
    {
        return $this->hasMany(Penyewaan::class);
    }
}
