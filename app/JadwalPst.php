<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JadwalPst extends Model
{
    protected $table = 'jadwal_psts';

    protected $fillable = [
        'tanggal',
        'sesi',
        'niplama',
        'nama_petugas',
        'status_kehadiran',
        'alasan_berhalangan',
        'status_tukar',
        'pengganti_niplama',
        'pengganti_nama',
        'task_id',
        'penugasan_id',
        'waktu_kehadiran',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'sesi'    => 'integer',
        'waktu_kehadiran' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'niplama', 'niplama');
    }

    public function pengganti()
    {
        return $this->belongsTo(User::class, 'pengganti_niplama', 'niplama');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function getSesiLabelAttribute()
    {
        return $this->sesi === 1 ? 'Sesi 1 (Pagi 08:00 - 12:00)' : 'Sesi 2 (Siang 13:00 - 16:00)';
    }

    public function getJamMulaiAttribute()
    {
        return $this->sesi === 1 ? '08:00' : '13:00';
    }

    public function getJamSelesaiAttribute()
    {
        return $this->sesi === 1 ? '12:00' : '16:00';
    }
}
