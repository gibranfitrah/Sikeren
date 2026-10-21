<?php

namespace App\Support;

use App\User;
use Illuminate\Support\Facades\Schema;

class SimpatiRoles
{
    /** Kode grup petugas layanan => Label. */
    public const GRUPS = [
        'pst'    => 'Petugas PST',
        'ppid'   => 'Petugas PPID',
        'satpam' => 'Petugas Satpam',
        'cs'     => 'Cleaning Service (CS)',
    ];

    /** Seluruh role admin SIMPATI. */
    public const ALL = [
        'super_admin',
        'admin_sdm',
        'admin_pst',
        'admin_ppid',
        'admin_satpam_cs',
        'admin_ruangan',
        'admin_zoom',
    ];

    public const LABELS = [
        'super_admin'     => 'Super Admin',
        'admin_sdm'       => 'Admin SDM',
        'admin_pst'       => 'Admin PST',
        'admin_ppid'      => 'Admin PPID',
        'admin_satpam_cs' => 'Admin Satpam & CS',
        'admin_ruangan'   => 'Admin Ruangan',
        'admin_zoom'      => 'Admin Zoom',
    ];

    public const DESCRIPTIONS = [
        'super_admin'     => 'Akses penuh: seluruh petugas, jadwal, dan pengelolaan role admin SIMPATI.',
        'admin_sdm'       => 'Mengelola seluruh petugas layanan (PST, PPID, Satpam, CS) dan jadwalnya.',
        'admin_pst'       => 'Mengelola petugas dan jadwal Petugas PST.',
        'admin_ppid'      => 'Mengelola petugas dan jadwal Petugas PPID.',
        'admin_satpam_cs' => 'Mengelola petugas dan jadwal Satpam serta Cleaning Service.',
        'admin_ruangan'   => 'Mengakses dashboard SIMPATI (lihat); pengelolaan ruangan tetap pada modul Booking Ruangan.',
        'admin_zoom'      => 'Mengakses dashboard SIMPATI (lihat); pengelolaan Zoom tetap pada modul Booking Ruangan.',
    ];

    /** Grup petugas yang boleh dikelola tiap role. */
    public const MANAGES = [
        'super_admin'     => ['pst', 'ppid', 'satpam', 'cs'],
        'admin_sdm'       => ['pst', 'ppid', 'satpam', 'cs'],
        'admin_pst'       => ['pst'],
        'admin_ppid'      => ['ppid'],
        'admin_satpam_cs' => ['satpam', 'cs'],
        'admin_ruangan'   => [],
        'admin_zoom'      => [],
    ];

    public static function label(string $role): string
    {
        return self::LABELS[$role] ?? $role;
    }

    public static function grupLabel(string $grup): string
    {
        return self::GRUPS[$grup] ?? strtoupper($grup);
    }

    /** Daftar role milik user (kosong bila tabel belum tersedia). */
    public static function userRoles(User $user): array
    {
        if ($user === null || !Schema::hasTable('simpati_admin_roles')) {
            return [];
        }

        return \DB::table('simpati_admin_roles')
            ->where('user_id', $user->id)
            ->pluck('role')
            ->filter(fn ($r) => in_array($r, self::ALL))
            ->values()
            ->all();
    }

    public static function hasAnyRole(User $user): bool
    {
        return count(self::userRoles($user)) > 0;
    }

    public static function hasRole(User $user, string $role): bool
    {
        return in_array($role, self::userRoles($user), true);
    }

    /** Semua grup petugas yang boleh dikelola user (Administrator sistem = semua). */
    public static function manageableGrups(User $user): array
    {
        if ($user === null) {
            return [];
        }

        if ($user->isAdmin()) {
            return array_keys(self::GRUPS);
        }

        $grups = [];
        foreach (self::userRoles($user) as $role) {
            foreach (self::MANAGES[$role] ?? [] as $grup) {
                $grups[] = $grup;
            }
        }

        return array_values(array_unique($grups));
    }

    public static function canManageGrup(User $user, string $grup): bool
    {
        return in_array($grup, self::manageableGrups($user), true);
    }

    /** Pengelolaan role admin hanya untuk Super Admin / Administrator sistem. */
    public static function canManageAdmins(User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->isAdmin() || self::hasRole($user, 'super_admin');
    }
}
