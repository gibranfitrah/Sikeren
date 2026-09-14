<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaKetuaTim extends Model
{
    use HasFactory;

    protected $table = 'agenda_ketua_tim';

    protected $guarded = ['id'];
}