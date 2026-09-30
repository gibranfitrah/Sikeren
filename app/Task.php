<?php
 
namespace App;
 
use Illuminate\Database\Eloquent\Model;
 
class Task extends Model
{
    protected $appends = ["open"];

    protected $fillable = [
        'nomor', 'duration', 'surat', 'owners', 'text', 'tim', 'wilayah', 'agenda', 'tempat', 
        'pemimpin', 'notulis', 'start_date', 'date_akhir', 'start_jam', 'end_jam', 'notulen', 
        'jenis', 'status', 'alasan_status', 'parent_id', 'parent', 'jenis_kegiatan', 'status_ruangan', 'status_pemimpin', 
        'surat_id', 'venue_id', 'tim_dokumentasi', 'penanggung_jawab', 'setuju_rapat', 'progress', 
        'notulen_selesai', 'materi_link', 'foto_link', 'jenis_tujuan', 'tujuan', 'sortorder'
    ];

    public function subKegiatans()
    {
        return $this->hasMany(SubKegiatan::class, 'task_id');
    }

    public function roomBooking()
    {
        return $this->hasOne(RoomBooking::class, 'task_id');
    }

    public function penugasans()
    {
        return $this->hasMany(\App\penugasan::class, 'id_kegiatan');
    }

    public function getIsPstAttribute()
    {
        return ($this->jenis_kegiatan === 'Pelayanan Statistik Terpadu (PST)' || 
                stripos($this->text ?? '', 'PST') !== false || 
                stripos($this->agenda ?? '', 'PST') !== false);
    }

    public function getPetugasListAttribute()
    {
        $names = $this->penugasans->pluck('peserta')->filter()->unique()->values()->all();
        if (empty($names) && !empty($this->penanggung_jawab)) {
            $names = [$this->penanggung_jawab];
        }
        return $names;
    }

    public function getWilayahListAttribute()
    {
        if (empty($this->wilayah)) {
            return [];
        }
        $decoded = json_decode($this->wilayah, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return array_filter(array_map('trim', explode(',', $this->wilayah)));
    }

    public function setownersAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['owners'] = json_encode($value);
        } else {
            $this->attributes['owners'] = $value;
        }
    }

    public function getownersAttribute($value)
    {
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return $value;
    }

    public function getOpenAttribute(){
        return true;
    }

    /**
     * Otomatis sinkronisasi seluruh kegiatan yang sudah melewati tanggal atau jam pelaksanaannya
     * menjadi status 'Selesai' (progress 100%), baik itu kegiatan PST maupun kegiatan tim lainnya.
     */
    public static function updateExpiredKegiatanStatus()
    {
        try {
            $now = \Carbon\Carbon::now();
            $todayStr = $now->format('Y-m-d');
            $nowTimeStr = $now->format('H:i:s');

            // 1. Seluruh kegiatan dari bulan Januari 2026 sampai akhir September 2026 statusnya selesai semua
            self::where('jenis', 'Kegiatan')
                ->where(function($q) {
                    $q->where('start_date', '<=', '2026-09-30')
                      ->orWhere('date_akhir', '<=', '2026-09-30');
                })
                ->where('status', '!=', 'Selesai')
                ->update(['status' => 'Selesai', 'progress' => 100]);

            // 2. Seluruh kegiatan (bukan Rapat) yang tanggal akhirnya sudah sebelum hari ini (< today)
            self::where('jenis', 'Kegiatan')
                ->where(function($q) use ($todayStr) {
                    $q->where(function($q1) use ($todayStr) {
                        $q1->whereNotNull('date_akhir')->where('date_akhir', '<', $todayStr);
                    })->orWhere(function($q2) use ($todayStr) {
                        $q2->whereNull('date_akhir')->where('start_date', '<', $todayStr);
                    });
                })
                ->where('status', '!=', 'Selesai')
                ->update(['status' => 'Selesai', 'progress' => 100]);

            // 2. Seluruh kegiatan (bukan Rapat) yang berakhir hari ini (= today) dan jam selesainya sudah lewat
            self::where('jenis', 'Kegiatan')
                ->where(function($q) use ($todayStr) {
                    $q->where('date_akhir', $todayStr)
                      ->orWhere(function($q2) use ($todayStr) {
                          $q2->whereNull('date_akhir')->where('start_date', $todayStr);
                      });
                })
                ->whereNotNull('end_jam')
                ->where('end_jam', '<=', $nowTimeStr)
                ->where('status', '!=', 'Selesai')
                ->update(['status' => 'Selesai', 'progress' => 100]);

            // 3. Kegiatan yang berakhir hari ini tanpa end_jam setelah jam kerja kantor (>= 17:00 WITA)
            if ($now->hour >= 17) {
                self::where('jenis', 'Kegiatan')
                    ->where(function($q) use ($todayStr) {
                        $q->where('date_akhir', $todayStr)
                          ->orWhere(function($q2) use ($todayStr) {
                              $q2->whereNull('date_akhir')->where('start_date', $todayStr);
                          });
                    })
                    ->whereNull('end_jam')
                    ->where('status', '!=', 'Selesai')
                    ->update(['status' => 'Selesai', 'progress' => 100]);
            }

            // 4. Sub-kegiatan yang sudah melewati batas waktu (end_date < today)
            \App\SubKegiatan::whereNotNull('end_date')
                ->where('end_date', '<', $todayStr)
                ->where('status', '!=', 'Selesai')
                ->update(['status' => 'Selesai', 'progress' => 100]);

        } catch (\Exception $e) {
            \Log::error('Gagal update status kegiatan lewat tanggal: ' . $e->getMessage());
        }
    }
}