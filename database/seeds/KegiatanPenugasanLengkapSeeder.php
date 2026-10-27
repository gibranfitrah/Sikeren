<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KegiatanPenugasanLengkapSeeder extends Seeder
{
    public function run()
    {
        $jsonPath = database_path('seeds/data/semua_kegiatan_penugasan.json');
        if (!file_exists($jsonPath)) {
            $altPath = database_path('seeders/data/semua_kegiatan_penugasan.json');
            if (file_exists($altPath)) {
                $jsonPath = $altPath;
            } else {
                $this->command->error("File data tidak ditemukan: " . $jsonPath);
                return;
            }
        }

        $raw = file_get_contents($jsonPath);
        $data = json_decode($raw, true);

        if (!$data || !isset($data['tasks'])) {
            $this->command->error("Format JSON data tidak valid!");
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Seed Tasks (100 kegiatan & rapat)
        if (Schema::hasTable('tasks') && !empty($data['tasks'])) {
            foreach ($data['tasks'] as $task) {
                DB::table('tasks')->updateOrInsert(
                    ['id' => $task['id']],
                    $task
                );
            }
            if (isset($this->command)) {
                $this->command->info("✓ " . count($data['tasks']) . " data tasks berhasil disinkronkan.");
            }
        }

        // 2. Seed Sub Kegiatan
        if (Schema::hasTable('sub_kegiatans') && !empty($data['sub_kegiatans'])) {
            foreach ($data['sub_kegiatans'] as $sub) {
                DB::table('sub_kegiatans')->updateOrInsert(
                    ['id' => $sub['id']],
                    $sub
                );
            }
            if (isset($this->command)) {
                $this->command->info("✓ " . count($data['sub_kegiatans']) . " data sub_kegiatans berhasil disinkronkan.");
            }
        }

        // 3. Seed Agenda Ketua Tim
        if (Schema::hasTable('agenda_ketua_tim') && !empty($data['agenda_ketua_tim'])) {
            foreach ($data['agenda_ketua_tim'] as $agenda) {
                DB::table('agenda_ketua_tim')->updateOrInsert(
                    ['id' => $agenda['id']],
                    $agenda
                );
            }
            if (isset($this->command)) {
                $this->command->info("✓ " . count($data['agenda_ketua_tim']) . " data agenda_ketua_tim berhasil disinkronkan.");
            }
        }

        // 4. Seed Penugasans (2962 penugasan pegawai)
        if (Schema::hasTable('penugasans') && !empty($data['penugasans'])) {
            $updateColumns = ['id_kegiatan', 'niplama', 'peserta', 'status_kehadiran', 'keterangan', 'waktu_kehadiran', 'updated_at'];
            $chunks = array_chunk($data['penugasans'], 250);
            foreach ($chunks as $chunk) {
                DB::table('penugasans')->upsert($chunk, ['id'], $updateColumns);
            }
            if (isset($this->command)) {
                $this->command->info("✓ " . count($data['penugasans']) . " data penugasans berhasil disinkronkan.");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
