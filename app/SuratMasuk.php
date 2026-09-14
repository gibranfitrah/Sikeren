<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratMasuk extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_surat', 'tanggal_surat', 'asal_surat', 'perihal', 
        'ringkasan', 'file_surat', 'nomor_agenda', 'lampiran'
    ];

    public function disposisis()
    {
        return $this->hasMany(Disposisi::class);
    }
}
