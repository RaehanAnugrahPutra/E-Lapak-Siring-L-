<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //
    protected $fillable = ['nama_event', 'tanggal_event', 'lokasi_event'];

    protected $table = 'events';

    protected $hidden = ['created_at', 'updated_at'];
}
