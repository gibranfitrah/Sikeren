<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'capacity'];

    /**
     * Relasi ke Matriks Kapasitas per Layout
     */
    public function capacities()
    {
        return $this->hasMany(RoomCapacity::class, 'venue_id');
    }
}
