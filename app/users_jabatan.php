<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class users_jabatan extends Model
{
    //
   
      protected $fillable = [
        'id_organisasi', 'id_users','id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
