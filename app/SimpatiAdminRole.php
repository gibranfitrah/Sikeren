<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SimpatiAdminRole extends Model
{
    protected $table = 'simpati_admin_roles';

    protected $fillable = ['user_id', 'role'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
