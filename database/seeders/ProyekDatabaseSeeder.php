<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class ProyekDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $proyekPath = 'D:/MAGANG/yang mau dijadikan database/proyek.xlsx';
        $anggotaPath = 'D:/MAGANG/yang mau dijadikan database/anggota_proyek.xlsx';

        if (!file_exists($proyekPath)) {
            $this->command->error("File tidak ditemukan: $proyekPath");
            return;
        }

        if (!file_exists($anggotaPath)) {
            $this->command->error("File tidak ditemukan: $anggotaPath");
            return;
        }

        $now = Carbon::now();

        // 1. IMPORT PROYEK
        $this->command->info("Membaca $proyekPath ...");
        $proyekSheet = IOFactory::load($proyekPath)->getActiveSheet()->toArray();
        $proyekHeader = array_shift($proyekSheet);

        $proyekData = [];
        $uniqueProyekIds = [];

        foreach ($proyekSheet as $r) {
            if (empty($r[0]) && empty($r[2])) continue;

            $id_tim     = trim((string)($r[0] ?? ''));
            $nm_tim     = trim((string)($r[1] ?? ''));
            $proyekid   = trim((string)($r[2] ?? ''));
            $namaproyek = trim((string)($r[3] ?? ''));

            if (empty($proyekid)) continue;

            if (!isset($uniqueProyekIds[$proyekid])) {
                $uniqueProyekIds[$proyekid] = true;
                $proyekData[] = [
                    'id_tim'     => $id_tim,
                    'nm_tim'     => $nm_tim,
                    'proyekid'   => $proyekid,
                    'namaproyek' => $namaproyek,
                    'status'     => 'Aktif',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($proyekData)) {
            DB::table('proyeks')->upsert(
                $proyekData,
                ['proyekid'],
                ['id_tim', 'nm_tim', 'namaproyek', 'status', 'updated_at']
            );
            $this->command->info("Berhasil mengimpor " . count($proyekData) . " proyek ke tabel proyeks.");
        }

        // 2. IMPORT ANGGOTA PROYEK
        $this->command->info("Membaca $anggotaPath ...");
        $anggotaSheet = IOFactory::load($anggotaPath)->getActiveSheet()->toArray();
        $anggotaHeader = array_shift($anggotaSheet);

        $anggotaData = [];
        $uniquePairs = [];

        foreach ($anggotaSheet as $r) {
            if (empty($r[0]) && empty($r[4])) continue;

            $id_tim       = trim((string)($r[0] ?? ''));
            $nm_tim       = trim((string)($r[1] ?? ''));
            $niplama      = trim((string)($r[2] ?? ''));
            $nama_lengkap = trim((string)($r[3] ?? ''));
            $proyekid     = trim((string)($r[4] ?? ''));
            $namaproyek   = trim((string)($r[5] ?? ''));

            if (empty($proyekid) || empty($niplama)) continue;

            $pairKey = $proyekid . '_' . $niplama;
            if (!isset($uniquePairs[$pairKey])) {
                $uniquePairs[$pairKey] = true;
                $anggotaData[] = [
                    'proyekid'     => $proyekid,
                    'namaproyek'   => $namaproyek,
                    'id_tim'       => $id_tim,
                    'nm_tim'       => $nm_tim,
                    'niplama'      => $niplama,
                    'nama_lengkap' => $nama_lengkap,
                    'peran'        => 'Anggota',
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }
        }

        if (!empty($anggotaData)) {
            $chunks = array_chunk($anggotaData, 500);
            foreach ($chunks as $chunk) {
                DB::table('anggota_proyeks')->upsert(
                    $chunk,
                    ['proyekid', 'niplama'],
                    ['namaproyek', 'id_tim', 'nm_tim', 'nama_lengkap', 'peran', 'updated_at']
                );
            }
            $this->command->info("Berhasil mengimpor " . count($anggotaData) . " penugasan anggota ke tabel anggota_proyeks.");
        }
    }
}
