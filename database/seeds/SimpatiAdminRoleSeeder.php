<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SimpatiAdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menetapkan role awal untuk admin SIMPATI:
     * - Administrator: Super Admin
     * - A. Ranuwirawan Rahim: Admin SDM
     * - Wa Ode Vitria Astika Sari: Admin PST & Admin SDM (contoh pegawai memegang >1 role)
     * - Budi Santoso: Admin PPID
     * - Ahmad Fauzi: Admin Satpam & CS
     * - Hendra Wijaya: Admin Ruangan
     * - Siti Aminah: Admin Zoom
     */
    public function run()
    {
        if (!Schema::hasTable('simpati_admin_roles')) {
            return;
        }

        $defaultRoles = [
            ['super_admin', 'Administrator'],
            ['admin_sdm', 'A. Ranuwirawan Rahim'],
            ['admin_pst', 'Wa Ode Vitria Astika Sari'],
            ['admin_sdm', 'Wa Ode Vitria Astika Sari'], // Satu pegawai memegang lebih dari satu role
            ['admin_ppid', 'Budi Santoso'],
            ['admin_satpam_cs', 'Ahmad Fauzi'],
            ['admin_ruangan', 'Hendra Wijaya'],
            ['admin_zoom', 'Siti Aminah'],
        ];

        foreach ($defaultRoles as [$role, $name]) {
            $user = User::where('nama_lengkap', 'like', "%{$name}%")->first();
            if (!$user && strtolower($name) === 'administrator') {
                $user = User::where('username', 'admin')->first();
            }

            if ($user) {
                DB::table('simpati_admin_roles')->updateOrInsert(
                    ['user_id' => $user->id, 'role' => $role],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
