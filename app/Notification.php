<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'niplama',
        'judul',
        'pesan',
        'link',
        'tipe',
        'is_read'
    ];
}