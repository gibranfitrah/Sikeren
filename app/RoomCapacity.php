<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomCapacity extends Model
{
    use HasFactory;

    /**
     * Layout kanonis sesuai matriks kapasitas final (spreadsheet + hybrid remote).
     * 4 utama dari spreadsheet + 3 tambahan dari preview SVG remote.
     */
    public const LAYOUTS = ['Theatre', 'Classroom', 'U-Shape', 'Boardroom', 'Round Table', 'Hollow Square', 'Custom Layout'];

    /**
     * Nilai konfigurasi sofa yang valid.
     */
    public const SOFA_TANPA = 'tanpa_sofa';
    public const SOFA_DENGAN = 'dengan_sofa';

    protected $fillable = [
        'venue_id',
        'layout',
        'capacity_without_sofa',
        'capacity_with_sofa',
        'with_sofa_available',
    ];

    protected $casts = [
        'with_sofa_available' => 'boolean',
    ];

    /**
     * Relasi ke Venue Ruangan
     */
    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    /**
     * Lookup kapasitas untuk kombinasi venue + layout + sofa.
     *
     * Return array:
     * - found (bool): baris matriks ditemukan
     * - available (bool): kombinasi sofa tersedia
     * - capacity (int|null): kapasitas berlaku (null bila tak tersedia / tak ditemukan)
     * - capacity_without_sofa / capacity_with_sofa / with_sofa_available
     *
     * NULL pada capacity_with_sofa TIDAK PERNAH dianggap 0.
     */
    public static function lookup($venueId, $layout, $sofaConfig = self::SOFA_TANPA)
    {
        $row = self::where('venue_id', $venueId)
            ->where('layout', $layout)
            ->first();

        if (!$row) {
            return [
                'found'                 => false,
                'available'             => false,
                'capacity'              => null,
                'capacity_without_sofa' => null,
                'capacity_with_sofa'    => null,
                'with_sofa_available'   => false,
            ];
        }

        if ($sofaConfig === self::SOFA_DENGAN) {
            $available = (bool) $row->with_sofa_available && !is_null($row->capacity_with_sofa);
            return [
                'found'                 => true,
                'available'             => $available,
                'capacity'              => $available ? (int) $row->capacity_with_sofa : null,
                'capacity_without_sofa' => (int) $row->capacity_without_sofa,
                'capacity_with_sofa'    => is_null($row->capacity_with_sofa) ? null : (int) $row->capacity_with_sofa,
                'with_sofa_available'   => (bool) $row->with_sofa_available,
            ];
        }

        return [
            'found'                 => true,
            'available'             => true,
            'capacity'              => (int) $row->capacity_without_sofa,
            'capacity_without_sofa' => (int) $row->capacity_without_sofa,
            'capacity_with_sofa'    => is_null($row->capacity_with_sofa) ? null : (int) $row->capacity_with_sofa,
            'with_sofa_available'   => (bool) $row->with_sofa_available,
        ];
    }

    /**
     * Daftar layout yang dikonfigurasi untuk sebuah venue.
     */
    public static function layoutsForVenue($venueId)
    {
        return self::where('venue_id', $venueId)
            ->orderBy('id', 'asc')
            ->get();
    }
}
