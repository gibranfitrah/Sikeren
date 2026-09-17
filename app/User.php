<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nama_lengkap', 'username', 'email', 'password', 'niplama', 'nipbaru', 'token_google'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function kegiatan()
    {
        return $this->hasMany(kegiatan::class);
    }

    public function users_jabatan()
    {
        return $this->hasMany(users_jabatan::class);
    }
    public function presensi()
    {
        return $this->hasMany(presensi::class, 'niplama', 'niplama');
    }

    public function getTeamNameAttribute()
    {
        $group = \DB::table('groups')
            ->where('niplama', $this->niplama)
            ->first();

        return $group ? $group->grup : null;
    }

    public function getRoleLabelAttribute()
    {
        $username = strtolower(trim($this->username ?? ''));
        $email = strtolower(trim($this->email ?? ''));
        $nama = strtolower(trim($this->nama_lengkap ?? ''));

        // 1. Check Admin
        if (
            $username === 'admin' ||
            str_contains($email, 'admin') ||
            (isset($this->level) && strtolower($this->level) === 'admin')
        ) {
            return 'Administrator';
        }

        // 2. Check Jabatan from users_jabatan
        $jabatan = \DB::table('users_jabatan')
            ->where('id_users', $this->id)
            ->orderBy('id', 'desc')
            ->value('nm_jabatan');

        if (!empty($jabatan)) {
            $jLow = strtolower($jabatan);
            if (str_contains($jLow, 'kepala bps') || str_contains($jLow, 'kepala badan')) {
                return 'Pimpinan / Kepala BPS';
            }
            if (str_contains($jLow, 'ketua tim') || str_contains($jLow, 'penanggung jawab')) {
                return 'Ketua Tim / PJ';
            }
            if (str_contains($jLow, 'kepala bidang') || str_contains($jLow, 'kepala bagian') || str_contains($jLow, 'kepala seksi')) {
                return $jabatan;
            }
        }

        // 3. Check if user is a designated Ketua Tim / PJ across system
        $namaLengkap = trim($this->nama_lengkap ?? '');
        $isPJ = false;

        if (!empty($namaLengkap)) {
            // (A) Check agenda_ketua_tim
            $isPJ = \DB::table('agenda_ketua_tim')
                ->where('pj', 'LIKE', '%' . $namaLengkap . '%')
                ->exists();

            // (B) Check tasks (penanggung_jawab or pemimpin)
            if (!$isPJ) {
                $isPJ = \DB::table('tasks')
                    ->where('penanggung_jawab', 'LIKE', '%' . $namaLengkap . '%')
                    ->orWhere('pemimpin', 'LIKE', '%' . $namaLengkap . '%')
                    ->exists();
            }

            // (C) Check sub_kegiatans (pj)
            if (!$isPJ) {
                $isPJ = \DB::table('sub_kegiatans')
                    ->where('pj', 'LIKE', '%' . $namaLengkap . '%')
                    ->exists();
            }
        }

        if ($isPJ || $nama === 'budi' || $nama === 'a. ranuwirawan rahim') {
            return 'Ketua Tim / PJ';
        }

        // 4. Check Group / Team (Anggota Tim)
        $team = $this->team_name;
        if (!empty($team)) {
            return 'Anggota Tim (' . $team . ')';
        }

        return 'Pegawai BPS';
    }

    public function getRoleBadgeVariantAttribute()
    {
        $role = $this->role_label;
        if ($role === 'Administrator') {
            return 'danger';
        } elseif (str_contains($role, 'Pimpinan') || str_contains($role, 'Kepala BPS')) {
            return 'primary';
        } elseif (str_contains($role, 'Ketua Tim') || str_contains($role, 'PJ')) {
            return 'warning';
        } elseif (str_contains($role, 'Anggota Tim')) {
            return 'success';
        }
        return 'neutral';
    }

    public function getFormattedNipAttribute()
    {
        return $this->nipbaru ?: ($this->niplama ?: '-');
    }

    public function isKetuaTimOrPj()
    {
        $role = $this->role_label;
        if (
            $role === 'Administrator' ||
            str_contains($role, 'Pimpinan') ||
            str_contains($role, 'Kepala') ||
            str_contains($role, 'Ketua Tim') ||
            str_contains($role, 'PJ')
        ) {
            return true;
        }

        // Check if user has jabatan containing Madya / Ahli Madya
        $hasMadyaJabatan = \DB::table('users_jabatan')
            ->where('id_users', $this->id)
            ->where(function($q) {
                $q->where('nm_jabatan', 'LIKE', '%Madya%')
                  ->orWhere('nm_jabatan', 'LIKE', '%Ketua%')
                  ->orWhere('nm_jabatan', 'LIKE', '%Kepala%')
                  ->orWhere('nm_jabatan', 'LIKE', '%Koordinator%')
                  ->orWhere('nm_jabatan', 'LIKE', '%Penanggung%');
            })
            ->exists();

        if ($hasMadyaJabatan) {
            return true;
        }

        // Check if user is assigned as PJ / Pemimpin / Notulis in any task
        $nama = trim($this->nama_lengkap ?? '');
        if (!empty($nama)) {
            $hasTaskAsPJ = \DB::table('tasks')
                ->where('penanggung_jawab', 'LIKE', '%' . $nama . '%')
                ->orWhere('pemimpin', 'LIKE', '%' . $nama . '%')
                ->orWhere('notulis', 'LIKE', '%' . $nama . '%')
                ->exists();
            if ($hasTaskAsPJ) {
                return true;
            }
        }

        return false;
    }

    public function isEligiblePJ()
    {
        return $this->isKetuaTimOrPj();
    }

    public static function getEligiblePJs()
    {
        $all = self::orderBy('nama_lengkap', 'asc')->get();
        return $all->filter(function ($u) {
            return $u->isEligiblePJ();
        });
    }

    public function canAccessDetailKegiatan($task = null)
    {
        // 1. Super Admin has full access
        $username = strtolower(trim($this->username ?? ''));
        $email = strtolower(trim($this->email ?? ''));
        if (
            $username === 'admin' ||
            str_contains($email, 'admin') ||
            (isset($this->level) && strtolower($this->level) === 'admin')
        ) {
            return true;
        }

        // 2. If task provided, check if user is specifically the creator/PJ/Pemimpin/Notulis/Tim Dokumentasi of THIS task
        if ($task) {
            $nama = strtolower(trim($this->nama_lengkap ?? ''));
            
            $pj = strtolower(trim($task->penanggung_jawab ?? ''));
            $pemimpin = strtolower(trim($task->pemimpin ?? ''));
            $notulis = strtolower(trim($task->notulis ?? ''));
            $timDok = strtolower(trim($task->tim_dokumentasi ?? ''));

            if (!empty($nama)) {
                if (
                    ($pj && (str_contains($pj, $nama) || str_contains($nama, $pj))) ||
                    ($pemimpin && (str_contains($pemimpin, $nama) || str_contains($nama, $pemimpin))) ||
                    ($notulis && (str_contains($notulis, $nama) || str_contains($nama, $notulis))) ||
                    ($timDok && (str_contains($timDok, $nama) || str_contains($nama, $timDok)))
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
