<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RoomBooking extends Model
{
    use HasFactory;

    protected $table = 'room_bookings';

    protected $fillable = [
        'task_id',
        'nama_acara',
        'penyelenggara',
        'jumlah_peserta',
        'tipe_pertemuan',
        'venue_id',
        'nama_ruangan',
        'fasilitas',
        'layout_meja',
        'setup_podium',
        'special_requests',
        'zoom_account',
        'zoom_link',
        'zoom_meeting_id',
        'zoom_passcode',
        'zoom_host_key',
        'booking_date',
        'start_time',
        'end_time',
        'status',
        'keterangan',
        'created_by'
    ];

    /**
     * Konfigurasi Akun Zoom Resmi BPS Sultra
     */
    public static $zoomAccounts = [
        'zoom_1' => [
            'key'         => 'zoom_1',
            'name'        => 'Akun Zoom 1 (BPS Sultra 1)',
            'email'       => 'bps7400.zoom1@bps.go.id',
            'meeting_id'  => '842 9182 3011',
            'passcode'    => 'bps7400',
            'link'        => 'https://zoom.us/j/84291823011',
            'capacity'    => 300,
            'color'       => 'blue',
            'description' => 'Akun Utama • Kapasitas 300 Peserta • Cloud Recording Aktif'
        ],
        'zoom_2' => [
            'key'         => 'zoom_2',
            'name'        => 'Akun Zoom 2 (BPS Sultra 2)',
            'email'       => 'bps7400.zoom2@bps.go.id',
            'meeting_id'  => '913 7482 1190',
            'passcode'    => 'sultra2026',
            'link'        => 'https://zoom.us/j/91374821190',
            'capacity'    => 300,
            'color'       => 'indigo',
            'description' => 'Akun Cadangan • Kapasitas 300 Peserta • Breakout Rooms Aktif'
        ]
    ];

    /**
     * Relasi ke Rapat / Task
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Relasi ke Venue Ruangan
     */
    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    /**
     * Relasi ke User Pembuat
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessor untuk Data Setup Podium
     */
    public function getSetupPodiumDataAttribute()
    {
        if (empty($this->setup_podium)) {
            return [
                'tipe'               => 'Tanpa Podium',
                'jumlah_kursi'       => 0,
                'pasang_spanduk'     => false,
                'keterangan_spanduk' => null,
            ];
        }
        $decoded = json_decode($this->setup_podium, true);
        if (is_array($decoded)) {
            return array_merge([
                'tipe'               => 'Tanpa Podium',
                'jumlah_kursi'       => 0,
                'pasang_spanduk'     => false,
                'keterangan_spanduk' => null,
            ], $decoded);
        }
        return [
            'tipe'               => $this->setup_podium,
            'jumlah_kursi'       => 0,
            'pasang_spanduk'     => false,
            'keterangan_spanduk' => null,
        ];
    }

    /**
     * Accessor untuk Data Special Requests
     */
    public function getSpecialRequestsDataAttribute()
    {
        if (empty($this->special_requests)) {
            return [
                'items'     => [],
                'mic_count' => null,
                'lainnya'   => null,
            ];
        }
        $decoded = json_decode($this->special_requests, true);
        if (is_array($decoded)) {
            return array_merge([
                'items'     => [],
                'mic_count' => null,
                'lainnya'   => null,
            ], $decoded);
        }
        return [
            'items'     => array_filter(array_map('trim', explode(',', $this->special_requests))),
            'mic_count' => null,
            'lainnya'   => null,
        ];
    }

    /**
     * Cek apakah ada konflik jadwal untuk Ruangan atau Akun Zoom
     */
    public static function checkConflict($date, $startTime, $endTime, $venueId = null, $zoomAccount = null, $excludeBookingId = null)
    {
        $conflicts = [];

        // 1. Cek konflik ruangan fisik
        if ($venueId) {
            $roomQuery = self::where('booking_date', $date)
                ->where('venue_id', $venueId)
                ->where('status', '!=', 'Dibatalkan')
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where(function ($sub) use ($startTime, $endTime) {
                        $sub->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $startTime);
                    });
                });

            if ($excludeBookingId) {
                $roomQuery->where('id', '!=', $excludeBookingId);
            }

            $roomConflict = $roomQuery->first();
            if ($roomConflict) {
                $conflicts['room'] = "Ruangan {$roomConflict->nama_ruangan} sudah dibooking untuk '{$roomConflict->nama_acara}' pada jam {$roomConflict->start_time} - {$roomConflict->end_time}.";
            }
        }

        // 2. Cek konflik akun Zoom
        if ($zoomAccount && in_array($zoomAccount, ['zoom_1', 'zoom_2'])) {
            $zoomQuery = self::where('booking_date', $date)
                ->where('zoom_account', $zoomAccount)
                ->where('status', '!=', 'Dibatalkan')
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where(function ($sub) use ($startTime, $endTime) {
                        $sub->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $startTime);
                    });
                });

            if ($excludeBookingId) {
                $zoomQuery->where('id', '!=', $excludeBookingId);
            }

            $zoomConflict = $zoomQuery->first();
            if ($zoomConflict) {
                $zoomName = self::$zoomAccounts[$zoomAccount]['name'] ?? $zoomAccount;
                $conflicts['zoom'] = "{$zoomName} sudah digunakan untuk '{$zoomConflict->nama_acara}' pada jam {$zoomConflict->start_time} - {$zoomConflict->end_time}.";
            }
        }

        return $conflicts;
    }
}
