<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnggotaProyek extends Model
{
    protected $table = 'anggota_proyeks';

    protected $guarded = [];

    /**
     * Relasi balik ke Master Proyek
     */
    public function proyek()
    {
        return $this->belongsTo(Proyek::class, 'proyekid', 'proyekid');
    }

    /**
     * Relasi ke data User/Pegawai Sikeren berdasarkan niplama
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'niplama', 'niplama');
    }
}
