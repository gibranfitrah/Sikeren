<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SubKegiatan extends Model
{
    protected $table = 'sub_kegiatans';

    protected $fillable = [
        'task_id',
        'nama_sub',
        'tim',
        'pj',
        'start_date',
        'end_date',
        'anggota',
        'status',
        'progress',
        'keterangan'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function getAnggotaListAttribute()
    {
        if (empty($this->anggota)) {
            return [];
        }
        $decoded = json_decode($this->anggota, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return array_filter(array_map('trim', explode(',', $this->anggota)));
    }

    public function getDeadlineStatusAttribute()
    {
        if ($this->status === 'Selesai' || $this->progress >= 100) {
            return [
                'label' => 'Selesai',
                'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'dot'   => 'bg-emerald-500',
                'code'  => 'selesai'
            ];
        }

        if (empty($this->end_date)) {
            return [
                'label' => 'Sedang Berjalan',
                'class' => 'bg-blue-100 text-blue-800 border-blue-200',
                'dot'   => 'bg-blue-500',
                'code'  => 'running'
            ];
        }

        $today = Carbon::today();
        $endDate = Carbon::parse($this->end_date);
        $daysLeft = $today->diffInDays($endDate, false);

        if ($daysLeft < 0) {
            return [
                'label' => 'Terlambat ' . abs($daysLeft) . ' Hari',
                'class' => 'bg-rose-100 text-rose-800 border-rose-200',
                'dot'   => 'bg-rose-500',
                'code'  => 'overdue'
            ];
        } elseif ($daysLeft <= 3) {
            return [
                'label' => 'Mendekati Deadline (' . $daysLeft . ' Hari Lagi)',
                'class' => 'bg-amber-100 text-amber-800 border-amber-300 animate-pulse',
                'dot'   => 'bg-amber-500',
                'code'  => 'warning'
            ];
        } else {
            return [
                'label' => 'Sedang Berjalan (' . $daysLeft . ' Hari Lagi)',
                'class' => 'bg-sky-100 text-sky-800 border-sky-200',
                'dot'   => 'bg-sky-500',
                'code'  => 'running'
            ];
        }
    }
}