<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JadwalLayanan extends Model
{
    protected $table = 'jadwal_layanans';

    protected $fillable = [
        'grup', 'tanggal', 'sesi', 'jam_mulai', 'jam_selesai',
        'user_id', 'niplama', 'nama', 'status', 'sumber', 'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'sesi' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeGrup($query, string $grup)
    {
        return $query->where('grup', $grup);
    }

    public function scopeBulan($query, int $tahun, int $bulan)
    {
        return $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
    }
}
