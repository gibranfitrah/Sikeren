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
}