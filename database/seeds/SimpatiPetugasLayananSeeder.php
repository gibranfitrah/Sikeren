<?php

use App\PetugasLayanan;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SimpatiPetugasLayananSeeder extends Seeder
{
    /**
     * 61 NIP Pegawai PST BPS Provinsi Sulawesi Tenggara
     */
    public const PST_NIPS = [
        '340055487', '340060478', '340056789', '340061175', '340055490',
        '340064240', '340056221', '340020416', '340019247', '340058670',
        '340060052', '340056946', '340059507', '340056827', '340017755',
        '340050091', '340060106', '340055494', '340053916', '340020263',
        '340056305', '340056159', '340017759', '340056847', '340055818',
        '340020529', '340017268', '340055500', '340061380', '340014213',
        '340060193', '340017751', '340055840', '340060763', '340057915',
        '340060208', '340018478', '340057916', '340057141', '340060215',
        '340056681', '340058386', '340058865', '340018482', '340050193',
        '340053923', '340016400', '340062017', '340054412', '340017375',
        '340053274', '340066522', '340056900', '340058507', '340059787',
        '340053226', '340056478', '340055950', '340017760', '340054309',
        '340060440',
    ];

    /**
     * NIP Pegawai PPID (Bagian Umum & Layanan Informasi)
     */
    public const PPID_NIPS = [
        '340061538', '340055499', '340059047', '340057636', '340017756',
        '340018479', '340062815', '340065085', '340055503', '340055501',
        '340015652', '340013322', '340054307', '340019013', '340053150',
    ];

    /**
     * NIP Petugas Keamanan / Satpam
     */
    public const SATPAM_NIPS = [
        '340065822', // Tamzir
        '340065395', // Putu Suweda
        '340064008', // Abdul Djalil
        '340065093', // Mat Asdi
    ];

    /**
     * NIP Petugas Cleaning Service (CS)
     */
    public const CS_NIPS = [
        '340064806', // Iksan Djuku
        '340065657', // Sirnan Setiawan
        '340065223', // Muhammad Yusril
        '340063730', // Diase
    ];

    public function run(): void
    {
        if (!Schema::hasTable('petugas_layanans')) {
            return;
        }

        $this->seedPstOfficers();
        $this->seedPpidOfficers();
        $this->seedSatpamOfficers();
        $this->seedCsOfficers();
        $this->seedPstSchedules();
    }

    /**
     * Seed 61 Petugas PST
     */
    public function seedPstOfficers(): int
    {
        $pstUsers = $this->getUsersWithJabatan(self::PST_NIPS);
        $count = 0;

        foreach ($pstUsers as $u) {
            $exists = DB::table('petugas_layanans')
                ->where('user_id', $u->id)
                ->where('grup', 'pst')
                ->exists();

            if (!$exists) {
                DB::table('petugas_layanans')->insert([
                    'user_id'    => $u->id,
                    'niplama'    => $u->niplama,
                    'nipbaru'    => $u->nipbaru,
                    'nama'       => $u->nama_lengkap,
                    'jabatan'    => $u->nm_jabatan ?: 'Petugas PST BPS',
                    'grup'       => 'pst',
                    'is_aktif'   => 1,
                    'catatan'    => 'Petugas PST BPS Provinsi Sulawesi Tenggara',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Seed Petugas PPID
     */
    public function seedPpidOfficers(): int
    {
        $ppidUsers = $this->getUsersWithJabatan(self::PPID_NIPS);
        $count = 0;

        foreach ($ppidUsers as $u) {
            $exists = DB::table('petugas_layanans')
                ->where('user_id', $u->id)
                ->where('grup', 'ppid')
                ->exists();

            if (!$exists) {
                DB::table('petugas_layanans')->insert([
                    'user_id'    => $u->id,
                    'niplama'    => $u->niplama,
                    'nipbaru'    => $u->nipbaru,
                    'nama'       => $u->nama_lengkap,
                    'jabatan'    => $u->nm_jabatan ?: 'Petugas PPID BPS',
                    'grup'       => 'ppid',
                    'is_aktif'   => 1,
                    'catatan'    => 'Petugas PPID BPS Provinsi Sulawesi Tenggara',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Seed Petugas Satpam
     */
    public function seedSatpamOfficers(): int
    {
        $satpamUsers = $this->getUsersWithJabatan(self::SATPAM_NIPS);
        $count = 0;

        foreach ($satpamUsers as $u) {
            $exists = DB::table('petugas_layanans')
                ->where('user_id', $u->id)
                ->where('grup', 'satpam')
                ->exists();

            if (!$exists) {
                DB::table('petugas_layanans')->insert([
                    'user_id'    => $u->id,
                    'niplama'    => $u->niplama,
                    'nipbaru'    => $u->nipbaru,
                    'nama'       => $u->nama_lengkap,
                    'jabatan'    => $u->nm_jabatan ?: 'Petugas Pengamanan / Satpam',
                    'grup'       => 'satpam',
                    'is_aktif'   => 1,
                    'catatan'    => 'Petugas Satpam BPS Provinsi Sulawesi Tenggara',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Seed Petugas Cleaning Service
     */
    public function seedCsOfficers(): int
    {
        $csUsers = $this->getUsersWithJabatan(self::CS_NIPS);
        $count = 0;

        foreach ($csUsers as $u) {
            $exists = DB::table('petugas_layanans')
                ->where('user_id', $u->id)
                ->where('grup', 'cs')
                ->exists();

            if (!$exists) {
                DB::table('petugas_layanans')->insert([
                    'user_id'    => $u->id,
                    'niplama'    => $u->niplama,
                    'nipbaru'    => $u->nipbaru,
                    'nama'       => $u->nama_lengkap,
                    'jabatan'    => $u->nm_jabatan ?: 'Petugas Kebersihan / CS',
                    'grup'       => 'cs',
                    'is_aktif'   => 1,
                    'catatan'    => 'Petugas Cleaning Service BPS Provinsi Sulawesi Tenggara',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Seed Jadwal PST di jadwal_layanans dan jadwal_psts
     */
    public function seedPstSchedules(): void
    {
        if (!Schema::hasTable('jadwal_layanans')) {
            return;
        }

        // Cek apakah jadwal PST untuk bulan ini sudah ada
        $now = Carbon::now();
        $thisMonth = $now->format('Y-m');
        $hasSchedules = DB::table('jadwal_layanans')
            ->where('grup', 'pst')
            ->where('tanggal', 'like', $thisMonth . '-%')
            ->exists();

        if ($hasSchedules) {
            return;
        }

        // Ambil petugas PST aktif
        $pstOfficers = PetugasLayanan::where('grup', 'pst')->where('is_aktif', true)->get();
        if ($pstOfficers->isEmpty()) {
            return;
        }

        $totalOfficers = $pstOfficers->count();
        $officerIdx = 0;

        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            // Lewati hari Sabtu dan Minggu (PST hanya Senin-Jumat)
            if ($date->isWeekend()) {
                continue;
            }

            $tglStr = $date->toDateString();

            // Sesuai ketentuan: Petugas PST 4 orang per hari (2 Sesi 1, 2 Sesi 2)
            // Sesi 1: 08:00 - 12:00 WITA
            for ($s = 1; $s <= 2; $s++) {
                $officer = $pstOfficers[$officerIdx % $totalOfficers];
                $officerIdx++;

                DB::table('jadwal_layanans')->insert([
                    'grup'        => 'pst',
                    'tanggal'     => $tglStr,
                    'sesi'        => 1,
                    'jam_mulai'   => '08:00:00',
                    'jam_selesai' => '12:00:00',
                    'user_id'     => $officer->user_id,
                    'niplama'     => $officer->niplama,
                    'nama'        => $officer->nama,
                    'status'      => 'Bertugas',
                    'sumber'      => 'sistem',
                    'keterangan'  => 'Jadwal PST Sesi 1 (Pagi)',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                if (Schema::hasTable('jadwal_psts')) {
                    DB::table('jadwal_psts')->insert([
                        'tanggal'          => $tglStr,
                        'sesi'             => 1,
                        'niplama'          => $officer->niplama,
                        'nama_petugas'     => $officer->nama,
                        'status_kehadiran' => 'Terjadwal',
                        'status_tukar'     => 'none',
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }

            // Sesi 2: 13:00 - 16:00 WITA
            for ($s = 1; $s <= 2; $s++) {
                $officer = $pstOfficers[$officerIdx % $totalOfficers];
                $officerIdx++;

                DB::table('jadwal_layanans')->insert([
                    'grup'        => 'pst',
                    'tanggal'     => $tglStr,
                    'sesi'        => 2,
                    'jam_mulai'   => '13:00:00',
                    'jam_selesai' => '16:00:00',
                    'user_id'     => $officer->user_id,
                    'niplama'     => $officer->niplama,
                    'nama'        => $officer->nama,
                    'status'      => 'Bertugas',
                    'sumber'      => 'sistem',
                    'keterangan'  => 'Jadwal PST Sesi 2 (Siang)',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                if (Schema::hasTable('jadwal_psts')) {
                    DB::table('jadwal_psts')->insert([
                        'tanggal'          => $tglStr,
                        'sesi'             => 2,
                        'niplama'          => $officer->niplama,
                        'nama_petugas'     => $officer->nama,
                        'status_kehadiran' => 'Terjadwal',
                        'status_tukar'     => 'none',
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Helper mengambil pegawai beserta jabatan terbarunya
     */
    private function getUsersWithJabatan(array $nips)
    {
        return DB::table('users')
            ->leftJoin(DB::raw('(SELECT uj1.id_users, uj1.nm_jabatan FROM users_jabatan uj1 INNER JOIN (SELECT id_users, MAX(id) as max_id FROM users_jabatan GROUP BY id_users) uj2 ON uj1.id = uj2.max_id) as latest_jabatan'), 'users.id', '=', 'latest_jabatan.id_users')
            ->whereIn('users.niplama', $nips)
            ->select('users.id', 'users.niplama', 'users.nipbaru', 'users.nama_lengkap', 'latest_jabatan.nm_jabatan')
            ->orderBy('users.nama_lengkap')
            ->get();
    }
}
