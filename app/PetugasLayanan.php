<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PetugasLayanan extends Model
{
    protected $table = 'petugas_layanans';

    protected $fillable = [
        'user_id', 'niplama', 'nipbaru', 'nama', 'jabatan', 'grup', 'is_aktif', 'catatan',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeGrup($query, string $grup)
    {
        return $query->where('grup', $grup);
    }

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }
}
