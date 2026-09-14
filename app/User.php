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

        // 3. Check if user is known Ketua Tim / PJ
        $isPJ = \DB::table('agenda_ketua_tim')
            ->where('pj', 'LIKE', '%' . $this->nama_lengkap . '%')
            ->exists();

        if ($isPJ || $nama === 'budi' || $nama === 'a. ranuwirawan rahim') {
            return 'Ketua Tim / PJ';
        }

        // 4. Check Group / Team
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
}
