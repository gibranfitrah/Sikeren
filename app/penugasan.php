<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class penugasan extends Model
{
    //
   
    protected $fillable = [
        'id', 'id_kegiatan', 'niplama', 'peserta', 'status_kehadiran', 'keterangan', 'waktu_kehadiran'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'niplama', 'niplama');
    }

    
}
