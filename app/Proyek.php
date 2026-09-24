<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Proyek extends Model
{
    protected $table = 'proyeks';

    protected $guarded = [];

    /**
     * Relasi ke seluruh anggota penugasan proyek
     */
    public function anggota()
    {
        return $this->hasMany(AnggotaProyek::class, 'proyekid', 'proyekid');
    }

    /**
     * Relasi ke Pegawai Penanggung Jawab (PJ) jika ada NIP
     */
    public function pjUser()
    {
        return $this->belongsTo(User::class, 'pj_nip', 'niplama');
    }
}
