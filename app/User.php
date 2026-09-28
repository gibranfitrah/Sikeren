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

        // 1. Check Admin
        if (
            $username === 'admin' ||
            str_contains($email, 'admin') ||
            (isset($this->level) && strtolower($this->level) === 'admin')
        ) {
            return 'Administrator';
        }

        // 2. Check Jabatan resmi AKTIF from users_jabatan (misal: Kepala BPS Provinsi, Kepala Bagian Umum, Statistisi Ahli Madya, dll)
        $jabatan = \DB::table('users_jabatan')
            ->where('id_users', $this->id)
            ->where('is_active', 1)
            ->orderBy('id', 'desc')
            ->value('nm_jabatan');

        if (!empty($jabatan)) {
            $jTrim = trim($jabatan);
            if (!empty($jTrim)) {
                return $jTrim;
            }
        }

        // Fallback jika tidak ada record is_active = 1
        $jabatanFallback = \DB::table('users_jabatan')
            ->where('id_users', $this->id)
            ->orderBy('id', 'desc')
            ->value('nm_jabatan');

        if (!empty($jabatanFallback)) {
            $jTrim = trim($jabatanFallback);
            if (!empty($jTrim) && stripos($jTrim, 'purnatugas') === false && stripos($jTrim, 'mantan') === false) {
                return $jTrim;
            }
        }

        // 3. Check Group / Team (misal: Fungsi Neraca, Fungsi IPDS, Tim Nerwilis, Bagian Umum)
        $team = $this->team_name;
        if (!empty($team)) {
            $tTrim = trim($team);
            if (!empty($tTrim)) {
                return $tTrim;
            }
        }

        // Jika tidak diketahui bagian atau jabatannya, KOSONGKAN (jangan tulis Ketua Tim / PJ atau Pegawai BPS)
        return '';
    }

    public function getSelectOptionLabelAttribute()
    {
        $nip = $this->formatted_nip;
        $label = $this->nama_lengkap;
        if (!empty($nip) && $nip !== '-') {
            $label .= " ({$nip})";
        }

        $role = trim($this->role_label ?? '');
        if (!empty($role)) {
            $label .= " - {$role}";
        }

        return $label;
    }

    public function getRoleBadgeVariantAttribute()
    {
        $role = strtolower($this->role_label);
        if ($role === 'administrator') {
            return 'danger';
        } elseif (str_contains($role, 'pimpinan') || str_contains($role, 'kepala')) {
            return 'primary';
        } elseif (str_contains($role, 'ketua') || str_contains($role, 'madya') || str_contains($role, 'koordinator')) {
            return 'warning';
        } elseif (!empty($role)) {
            return 'success';
        }
        return 'neutral';
    }

    public function getFormattedNipAttribute()
    {
        return $this->nipbaru ?: ($this->niplama ?: '-');
    }

    public function isAdmin()
    {
        $username = strtolower(trim($this->username ?? ''));
        $email    = strtolower(trim($this->email ?? ''));
        $nama     = strtolower(trim($this->nama_lengkap ?? ''));

        return $username === 'admin' ||
               str_contains($email, 'admin') ||
               $nama === 'administrator' ||
               (isset($this->level) && strtolower($this->level) === 'admin') ||
               $this->role_label === 'Administrator';
    }

    public function isKetuaTimOrPj()
    {
        // Admin adalah Superuser/Sistem dan BUKAN Pegawai BPS yang dapat dijadikan PJ/Ketua Tim
        if ($this->isAdmin()) {
            return false;
        }

        // Mantan pimpinan purnatugas (seperti Ibu Agnes Widiastuti) bukan lagi PJ/Ketua Tim aktif
        if (stripos($this->nama_lengkap, 'Agnes') !== false) {
            return false;
        }

        // 1. Cek jabatan AKTIF apakah berposisi pimpinan / ketua tim / madya
        if ($this->isEligiblePJ()) {
            return true;
        }

        // 2. Cek apakah pernah/sedang ditunjuk sebagai PJ / Pemimpin di kegiatan / rapat
        $nama = trim($this->nama_lengkap ?? '');
        if (!empty($nama)) {
            $isPJ = \DB::table('agenda_ketua_tim')
                ->where('pj', 'LIKE', '%' . $nama . '%')
                ->exists();

            if (!$isPJ) {
                $isPJ = \DB::table('tasks')
                    ->where(function($q) use ($nama) {
                        $q->where('penanggung_jawab', 'LIKE', '%' . $nama . '%')
                          ->orWhere('pemimpin', 'LIKE', '%' . $nama . '%');
                    })
                    ->exists();
            }

            if (!$isPJ) {
                $isPJ = \DB::table('sub_kegiatans')
                    ->where('pj', 'LIKE', '%' . $nama . '%')
                    ->exists();
            }

            if ($isPJ) {
                return true;
            }
        }

        return false;
    }

    public function isEligiblePJ()
    {
        if ($this->isAdmin()) {
            return false;
        }

        // Mantan pimpinan purnatugas bukan eligible PJ
        if (stripos($this->nama_lengkap, 'Agnes') !== false) {
            return false;
        }

        // Ambil jabatan AKTIF user dari users_jabatan (bukan histori non-aktif lama)
        $latestJabatan = \DB::table('users_jabatan')
            ->where('id_users', $this->id)
            ->where('is_active', 1)
            ->orderBy('id', 'desc')
            ->value('nm_jabatan');

        if (empty($latestJabatan)) {
            return false;
        }

        $jLower = strtolower($latestJabatan);
        return str_contains($jLower, 'madya') ||
               str_contains($jLower, 'ketua tim') ||
               str_contains($jLower, 'kepala') ||
               str_contains($jLower, 'koordinator');
    }

    public function canAccessSimpati()
    {
        return $this->isAdmin() || $this->isKetuaTimOrPj();
    }

    /**
     * Scope query untuk mengambil seluruh Pegawai BPS (non-admin)
     */
    public function scopeBpsStaff($query)
    {
        return $query->where('username', '!=', 'admin')
                     ->where('nama_lengkap', '!=', 'Administrator')
                     ->where('email', 'not like', '%admin%');
    }

    /**
     * Mengambil daftar seluruh Pegawai BPS resmi (tanpa akun Administrator)
     */
    public static function getPegawaiBps()
    {
        $all = self::bpsStaff()->orderBy('nama_lengkap', 'asc')->get();
        return $all->filter(function ($u) {
            return !$u->isAdmin();
        })->values();
    }

    /**
     * Mengambil daftar seluruh Pejabat / Ketua Tim / Ahli Madya (Eligible PJ) BPS
     */
    public static function getEligiblePJs()
    {
        $all = self::getPegawaiBps();
        return $all->filter(function ($u) {
            return $u->isEligiblePJ();
        })->sortBy(function ($u) {
            $role = strtolower($u->role_label);
            // Prioritas urutan: Kepala BPS Provinsi nomor 1, Kepala Bagian Umum nomor 2, Ahli Madya nomor 3, dsb
            if (str_contains($role, 'kepala bps provinsi')) return '001_' . $u->nama_lengkap;
            if (str_contains($role, 'kepala bagian umum')) return '002_' . $u->nama_lengkap;
            if (str_contains($role, 'ahli madya')) return '003_' . $u->nama_lengkap;
            if (str_contains($role, 'madya')) return '004_' . $u->nama_lengkap;
            if (str_contains($role, 'ketua tim')) return '005_' . $u->nama_lengkap;
            if (str_contains($role, 'kepala')) return '006_' . $u->nama_lengkap;
            return '099_' . $u->nama_lengkap;
        })->values();
    }

    /**
     * Mendapatkan slug nama huruf kecil tanpa gelar untuk pola password default (misal: hadisusanto123)
     */
    public function getDefaultPasswordSlug()
    {
        $clean = explode(',', $this->nama_lengkap ?? '')[0];
        $clean = preg_replace('/^(Dr\.|Drs\.|Ir\.|Prof\.|H\.|Hj\.)\s+/i', '', trim($clean));
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $clean));
        if (empty($slug) && !empty($this->username)) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $this->username));
        }
        return $slug ?: 'user';
    }

    public function canAccessDetailKegiatan($task = null)
    {
        // 1. Super Administrator memiliki akses penuh ke seluruh kegiatan & rapat
        if ($this->isAdmin()) {
            return true;
        }

        if (!$task) {
            return false;
        }

        $nama      = strtolower(trim($this->nama_lengkap ?? ''));
        $username  = strtolower(trim($this->username ?? ''));
        $nip       = trim($this->niplama ?? '');
        $nipbaru   = trim($this->nipbaru ?? '');

        // 2. Cek apakah user secara spesifik DITUNJUK pada kegiatan / rapat ini:
        // A. Ditunjuk sebagai Penanggung Jawab (PJ)
        $pj = strtolower(trim($task->penanggung_jawab ?? ''));
        if (!empty($pj)) {
            if (
                (!empty($nama) && (str_contains($pj, $nama) || str_contains($nama, $pj))) ||
                (!empty($username) && str_contains($pj, $username)) ||
                (!empty($nip) && $nip !== '-' && str_contains($pj, $nip)) ||
                (!empty($nipbaru) && $nipbaru !== '-' && str_contains($pj, $nipbaru))
            ) {
                return true;
            }
        }

        // Cek juga di agenda_ketua_tim jika ada
        $isPjAgenda = \DB::table('agenda_ketua_tim')
            ->where('id', $task->id)
            ->where(function ($q) use ($nama, $nip, $nipbaru, $username) {
                if (!empty($nama)) $q->where('pj', 'LIKE', '%' . $nama . '%');
                if (!empty($nip) && $nip !== '-') $q->orWhere('pj', 'LIKE', '%' . $nip . '%');
                if (!empty($nipbaru) && $nipbaru !== '-') $q->orWhere('pj', 'LIKE', '%' . $nipbaru . '%');
                if (!empty($username)) $q->orWhere('pj', 'LIKE', '%' . $username . '%');
            })
            ->exists();
        if ($isPjAgenda) {
            return true;
        }

        // B. Ditunjuk sebagai Pemimpin Rapat
        $pemimpin = strtolower(trim($task->pemimpin ?? ''));
        if (!empty($pemimpin)) {
            if (
                (!empty($nama) && (str_contains($pemimpin, $nama) || str_contains($nama, $pemimpin))) ||
                (!empty($username) && str_contains($pemimpin, $username)) ||
                (!empty($nip) && $nip !== '-' && str_contains($pemimpin, $nip)) ||
                (!empty($nipbaru) && $nipbaru !== '-' && str_contains($pemimpin, $nipbaru))
            ) {
                return true;
            }
        }

        // C. Ditunjuk sebagai Notulis
        $notulis = strtolower(trim($task->notulis ?? ''));
        if (!empty($notulis)) {
            if (
                (!empty($nama) && (str_contains($notulis, $nama) || str_contains($nama, $notulis))) ||
                (!empty($username) && str_contains($notulis, $username)) ||
                (!empty($nip) && $nip !== '-' && str_contains($notulis, $nip)) ||
                (!empty($nipbaru) && $nipbaru !== '-' && str_contains($notulis, $nipbaru))
            ) {
                return true;
            }
        }

        // D. Ditunjuk sebagai Tim Dokumentasi
        $timDok = strtolower(trim($task->tim_dokumentasi ?? ''));
        if (!empty($timDok)) {
            if (
                (!empty($nama) && (str_contains($timDok, $nama) || str_contains($nama, $timDok))) ||
                (!empty($username) && str_contains($timDok, $username)) ||
                (!empty($nip) && $nip !== '-' && str_contains($timDok, $nip)) ||
                (!empty($nipbaru) && $nipbaru !== '-' && str_contains($timDok, $nipbaru))
            ) {
                return true;
            }
        }

        // E. Ditunjuk sebagai Peserta / Anggota (di tabel penugasans)
        $isPesertaPenugasan = \DB::table('penugasans')
            ->where('id_kegiatan', $task->id)
            ->where(function ($q) use ($nip, $nipbaru, $nama) {
                if (!empty($nip) && $nip !== '-') {
                    $q->where('niplama', $nip);
                }
                if (!empty($nipbaru) && $nipbaru !== '-') {
                    $q->orWhere('niplama', $nipbaru);
                }
                if (!empty($nama)) {
                    $q->orWhere('peserta', $this->nama_lengkap);
                    $q->orWhere('peserta', 'LIKE', '%' . $nama . '%');
                }
            })
            ->exists();
        if ($isPesertaPenugasan) {
            return true;
        }

        // F. Ditunjuk di field owners (JSON / CSV / Array / Single Value)
        $rawOwners = $task->owners;
        if (!empty($rawOwners)) {
            $ownersList = [];
            if (is_array($rawOwners)) {
                $ownersList = $rawOwners;
            } elseif (is_string($rawOwners)) {
                $decoded = json_decode($rawOwners, true);
                if (is_array($decoded)) {
                    $ownersList = $decoded;
                } else {
                    $ownersList = array_filter(array_map('trim', explode(',', $rawOwners)));
                }
            } elseif (is_numeric($rawOwners)) {
                $ownersList = [(string) $rawOwners];
            }

            if (is_array($ownersList)) {
                foreach ($ownersList as $owner) {
                    if (is_array($owner) || is_object($owner)) {
                        $ownerStr = json_encode($owner);
                    } else {
                        $ownerStr = (string) $owner;
                    }
                    $ownClean = strtolower(trim($ownerStr));
                    if (
                        (!empty($nip) && $ownClean === strtolower($nip)) ||
                        (!empty($nipbaru) && $ownClean === strtolower($nipbaru)) ||
                        (!empty($nama) && (str_contains($ownClean, $nama) || str_contains($nama, $ownClean))) ||
                        (!empty($username) && $ownClean === $username) ||
                        (!empty($this->id) && $ownClean === (string)$this->id)
                    ) {
                        return true;
                    }
                }
            }
        }

        // 3. Jika kegiatan/rapat ini adalah kegiatan dari TIM KERJA user sendiri (bukan tim lain):
        if (!empty($task->tim) && $task->tim !== 'Umum') {
            $userTims = \DB::table('groups')
                ->where(function ($q) use ($nip, $nipbaru) {
                    if (!empty($nip) && $nip !== '-') {
                        $q->where('niplama', $nip);
                    }
                    if (!empty($nipbaru) && $nipbaru !== '-') {
                        $q->orWhere('niplama', $nipbaru);
                    }
                })
                ->pluck('grup')
                ->filter()
                ->map(fn($t) => strtolower(trim($t)))
                ->toArray();

            $taskTimLower = strtolower(trim($task->tim));
            foreach ($userTims as $userTim) {
                if (!empty($userTim) && (str_contains($taskTimLower, $userTim) || str_contains($userTim, $taskTimLower))) {
                    return true;
                }
            }
        }

        // Jika dari tim lain dan TIDAK DITUNJUK, maka akses ditolak
        return false;
    }
}
