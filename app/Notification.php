<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'niplama',
        'judul',
        'pesan',
        'url',
        'link',
        'tipe',
        'is_read'
    ];
}