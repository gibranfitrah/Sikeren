<?php

namespace Database\Seeders;

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
                if (isset($this->command)) {
                    $this->command->error("File data tidak ditemukan: " . $jsonPath);
                }
                return;
            }
        }

        $raw = file_get_contents($jsonPath);
        $data = json_decode($raw, true);

        if (!$data || !isset($data['tasks'])) {
            if (isset($this->command)) {
                $this->command->error("Format JSON data tidak valid!");
            }
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Seed Tasks (100 kegiatan & rapat)
        if (Schema::hasTable('tasks') && !empty($data['tasks'])) {
            $taskColumns = array_flip(Schema::getColumnListing('tasks'));
            foreach ($data['tasks'] as $task) {
                $filteredTask = array_intersect_key($task, $taskColumns);
                if (!empty($filteredTask['id'])) {
                    DB::table('tasks')->updateOrInsert(
                        ['id' => $filteredTask['id']],
                        $filteredTask
                    );
                }
            }
            if (isset($this->command)) {
                $this->command->info("✓ " . count($data['tasks']) . " data tasks berhasil disinkronkan.");
            }
        }

        // 2. Seed Sub Kegiatan
        if (Schema::hasTable('sub_kegiatans') && !empty($data['sub_kegiatans'])) {
            $subColumns = array_flip(Schema::getColumnListing('sub_kegiatans'));
            foreach ($data['sub_kegiatans'] as $sub) {
                $filteredSub = array_intersect_key($sub, $subColumns);
                if (!empty($filteredSub['id'])) {
                    DB::table('sub_kegiatans')->updateOrInsert(
                        ['id' => $filteredSub['id']],
                        $filteredSub
                    );
                }
            }
            if (isset($this->command)) {
                $this->command->info("✓ " . count($data['sub_kegiatans']) . " data sub_kegiatans berhasil disinkronkan.");
            }
        }

        // 3. Seed Agenda Ketua Tim
        if (Schema::hasTable('agenda_ketua_tim') && !empty($data['agenda_ketua_tim'])) {
            $agendaColumns = array_flip(Schema::getColumnListing('agenda_ketua_tim'));
            foreach ($data['agenda_ketua_tim'] as $agenda) {
                $filteredAgenda = array_intersect_key($agenda, $agendaColumns);
                if (!empty($filteredAgenda['id'])) {
                    DB::table('agenda_ketua_tim')->updateOrInsert(
                        ['id' => $filteredAgenda['id']],
                        $filteredAgenda
                    );
                }
            }
            if (isset($this->command)) {
                $this->command->info("✓ " . count($data['agenda_ketua_tim']) . " data agenda_ketua_tim berhasil disinkronkan.");
            }
        }

        // 4. Seed Penugasans (2962 penugasan pegawai)
        if (Schema::hasTable('penugasans') && !empty($data['penugasans'])) {
            $penugasanColumns = array_flip(Schema::getColumnListing('penugasans'));
            $allUpdateCols = ['id_kegiatan', 'niplama', 'peserta', 'status_kehadiran', 'keterangan', 'waktu_kehadiran', 'updated_at'];
            $updateColumns = array_values(array_filter($allUpdateCols, fn($col) => isset($penugasanColumns[$col])));
            
            $filteredPenugasans = array_map(fn($row) => array_intersect_key($row, $penugasanColumns), $data['penugasans']);
            $chunks = array_chunk($filteredPenugasans, 250);
            foreach ($chunks as $chunk) {
                DB::table('penugasans')->upsert($chunk, ['id'], $updateColumns);
            }
            if (isset($this->command)) {
                $this->command->info("✓ " . count($data['penugasans']) . " data penugasans berhasil disinkronkan.");
            }
        }

        // 5. Seed Petugas Layanan (SIMPATI)
        if (Schema::hasTable('petugas_layanans') && !empty($data['petugas_layanans'])) {
            $plColumns = array_flip(Schema::getColumnListing('petugas_layanans'));
            foreach ($data['petugas_layanans'] as $pl) {
                $filteredPl = array_intersect_key($pl, $plColumns);
                if (!empty($filteredPl['id'])) {
                    DB::table('petugas_layanans')->updateOrInsert(
                        ['id' => $filteredPl['id']],
                        $filteredPl
                    );
                }
            }
            if (isset($this->command)) {
                $this->command->info("✓ " . count($data['petugas_layanans']) . " data petugas_layanans berhasil disinkronkan.");
            }
        }

        // 6. Seed SIMPATI Admin Roles
        if (Schema::hasTable('simpati_admin_roles') && !empty($data['simpati_admin_roles'])) {
            $arColumns = array_flip(Schema::getColumnListing('simpati_admin_roles'));
            foreach ($data['simpati_admin_roles'] as $ar) {
                $filteredAr = array_intersect_key($ar, $arColumns);
                if (!empty($filteredAr['id'])) {
                    DB::table('simpati_admin_roles')->updateOrInsert(
                        ['id' => $filteredAr['id']],
                        $filteredAr
                    );
                }
            }
            if (isset($this->command)) {
                $this->command->info("✓ " . count($data['simpati_admin_roles']) . " data simpati_admin_roles berhasil disinkronkan.");
            }
        }

        // 7. Seed Jadwal Layanan (PST 8 orang per hari)
        if (Schema::hasTable('jadwal_layanans') && !empty($data['jadwal_layanans'])) {
            $jlColumns = array_flip(Schema::getColumnListing('jadwal_layanans'));
            DB::table('jadwal_layanans')->where('grup', 'pst')->delete();
            foreach ($data['jadwal_layanans'] as $jl) {
                $filteredJl = array_intersect_key($jl, $jlColumns);
                if (!empty($filteredJl['id'])) {
                    DB::table('jadwal_layanans')->updateOrInsert(
                        ['id' => $filteredJl['id']],
                        $filteredJl
                    );
                }
            }
            if (isset($this->command)) {
                $this->command->info("✓ " . count($data['jadwal_layanans']) . " data jadwal_layanans berhasil disinkronkan.");
            }
        }

        // 8. Seed Jadwal PST
        if (Schema::hasTable('jadwal_psts') && !empty($data['jadwal_psts'])) {
            $jpColumns = array_flip(Schema::getColumnListing('jadwal_psts'));
            DB::table('jadwal_psts')->delete();
            foreach ($data['jadwal_psts'] as $jp) {
                $filteredJp = array_intersect_key($jp, $jpColumns);
                if (!empty($filteredJp['id'])) {
                    DB::table('jadwal_psts')->updateOrInsert(
                        ['id' => $filteredJp['id']],
                        $filteredJp
                    );
                }
            }
            if (isset($this->command)) {
                $this->command->info("✓ " . count($data['jadwal_psts']) . " data jadwal_psts berhasil disinkronkan.");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
