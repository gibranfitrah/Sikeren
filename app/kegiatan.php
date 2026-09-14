<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class kegiatan extends Model
{
    //
   
      protected $fillable = [
        'nomor','title', 'agenda', 'tempat', 'pemimpin', 'notulis',  'start', 'end', 'start_jam', 'end_jam','notulen','jenis'
    ];

    public function setPesertaAttribute($value)
    {
        $this->attributes['peserta'] = json_encode($value);
    }

    public function getPesertaAttribute($value)
    {
        return $this->attributes['peserta'] = json_decode($value);
    }

    public function user()
{
    return $this->belongsTo(User::class);
}
}
