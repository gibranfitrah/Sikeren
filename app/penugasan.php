<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class penugasan extends Model
{
    //
   
      protected $fillable = [
        'id', 'id_kegiatan', 'niplama', 'peserta'
    ];

    
}
