<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disposisi extends Model
{
    use HasFactory;

    protected $fillable = [
        'surat_masuk_id', 'diteruskan_kepada', 'instruksi', 'status',
        'tingkat_keamanan', 'tgl_penyelesaian', 'opsi_disposisi',
        'diteruskan_kepada_tim', 'diteruskan_kepada_lainnya', 'diketahui'
    ];

    public function suratMasuk()
    {
        return $this->belongsTo(SuratMasuk::class);
    }
}
