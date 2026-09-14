<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class presensi extends Model
{
    //
    public $timestamps = false;
   
      protected $fillable = [
        'niplama', 'status', 'created_at'
    ];
    
    public function user()
{
    return $this->belongsTo(User::class, 'niplama', 'niplama');
}

    
}
