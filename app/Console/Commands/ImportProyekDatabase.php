<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Task;
use App\penugasan;
use App\User;
use App\master_group;
use App\group;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportProyekDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simpati:import-proyek-database {--force : Overwrite existing projects}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import kegiatan/proyek dari proyek.xlsx dan anggota dari anggota_proyek.xlsx ke database tasks dan penugasans';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dir = 'D:/MAGANG/yang mau dijadikan database/';
        $proyekFile = $dir . 'proyek.xlsx';
        $anggotaFile = $dir . 'anggota_proyek.xlsx';

        if (!file_exists($proyekFile) || !file_exists($anggotaFile)) {
            $this->error("File proyek.xlsx atau anggota_proyek.xlsx tidak ditemukan di $dir");
            return 1;
        }

        $this->info("1. Membaca anggota_proyek.xlsx...");
        $spAnggota = IOFactory::load($anggotaFile);
        $sheetA = $spAnggota->getActiveSheet();
        $highestRowA = $sheetA->getHighestRow();

        $membersByProyekId = [];
        for ($r = 2; $r <= $highestRowA; $r++) {
            $pId = trim((string)$sheetA->getCellByColumnAndRow(5, $r)->getValue());
            $nip = trim((string)$sheetA->getCellByColumnAndRow(3, $r)->getValue());
            $nama = trim((string)$sheetA->getCellByColumnAndRow(4, $r)->getValue());
            $tim = trim((string)$sheetA->getCellByColumnAndRow(2, $r)->getValue());

            if ($pId && $nama) {
                if (!isset($membersByProyekId[$pId])) {
                    $membersByProyekId[$pId] = [];
                }
                $membersByProyekId[$pId][] = [
                    'nip'  => $nip,
                    'nama' => $nama,
                    'tim'  => $tim,
                ];

                // Pastikan user ada di tabel users
                $user = User::where('niplama', $nip)->first();
                if (!$user && $nama) {
                    $cleanName = trim(explode(',', $nama)[0]);
                    $user = User::where('nama_lengkap', 'like', $cleanName . '%')->first();
                }

                if (!$user) {
                    $user = new User();
                    $user->nama_lengkap = $nama;
                    $user->niplama      = $nip ?: 'NIP' . rand(100000, 999999);
                    
                    // Generate nipbaru unik (18 digit)
                    $baseNipBaru = (strlen($nip) == 18) ? $nip : ('19' . str_pad($user->niplama, 16, '0', STR_PAD_RIGHT));
                    $candidateNipBaru = substr($baseNipBaru, 0, 18);
                    $counter = 1;
                    while (User::where('nipbaru', $candidateNipBaru)->exists()) {
                        $candidateNipBaru = substr($baseNipBaru, 0, 15) . str_pad($counter++, 3, '0', STR_PAD_LEFT);
                    }
                    $user->nipbaru      = $candidateNipBaru;
                    $user->token_id     = md5(uniqid($nip . time(), true));

                    // Generate unique username
                    $baseUser = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(',', $nama)[0]));
                    if (empty($baseUser)) {
                        $baseUser = 'user' . rand(100, 999);
                    }
                    $candidateUser = $baseUser;
                    $uCounter = 1;
                    while (User::where('username', $candidateUser)->exists()) {
                        $candidateUser = $baseUser . $uCounter++;
                    }
                    $user->username     = $candidateUser;
                    $user->email        = $candidateUser . '@bps.go.id';
                    // Password huruf kecil namanya + 123
                    $cleanPw = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(',', $nama)[0])) . '123';
                    $user->password     = \Illuminate\Support\Facades\Hash::make($cleanPw);
                    $user->save();
                }

                // Daftarkan ke tim di tabel groups
                $actualNip = $user->niplama;
                if ($tim && $actualNip) {
                    $existsInGroup = group::where('grup', $tim)->where('niplama', $actualNip)->exists();
                    if (!$existsInGroup) {
                        $g = new group();
                        $g->grup = $tim;
                        $g->niplama = $actualNip;
                        $g->save();
                    }
                }
            }
        }
        $this->info("   Berhasil mengindeks anggota untuk " . count($membersByProyekId) . " proyek.");

        $this->info("2. Membaca proyek.xlsx dan mengimpor ke tabel tasks...");
        $spProyek = IOFactory::load($proyekFile);
        $sheetP = $spProyek->getActiveSheet();
        $highestRowP = $sheetP->getHighestRow();

        $importedCount = 0;
        $updatedCount = 0;

        for ($r = 2; $r <= $highestRowP; $r++) {
            $idTim      = trim((string)$sheetP->getCellByColumnAndRow(1, $r)->getValue());
            $nmTim      = trim((string)$sheetP->getCellByColumnAndRow(2, $r)->getValue());
            $proyekId   = trim((string)$sheetP->getCellByColumnAndRow(3, $r)->getValue());
            $namaProyek = trim((string)$sheetP->getCellByColumnAndRow(4, $r)->getValue());

            if (empty($namaProyek)) {
                continue;
            }

            // Pastikan tim terdaftar di master_groups
            if ($nmTim) {
                $mg = master_group::where('grup', $nmTim)->first();
                if (!$mg) {
                    $mg = new master_group();
                    $mg->grup = $nmTim;
                    $mg->save();
                }
            }

            $members = $membersByProyekId[$proyekId] ?? [];
            $memberNips = array_unique(array_filter(array_column($members, 'nip')));
            $memberNames = array_unique(array_filter(array_column($members, 'nama')));

            $pjNama = !empty($memberNames) ? reset($memberNames) : 'Ketua Tim';

            // Cek apakah proyek sudah ada di tabel tasks
            $task = Task::where('text', $namaProyek)
                ->where('jenis', 'Kegiatan')
                ->first();

            if (!$task) {
                $task = new Task();
                $task->text             = $namaProyek;
                $task->agenda           = "Proyek Tim: " . $nmTim;
                $task->tim              = $nmTim;
                $task->jenis            = 'Kegiatan';
                $task->jenis_kegiatan   = 'Proyek Tim';
                $task->tempat           = 'BPS Provinsi Sulawesi Tenggara';
                $task->penanggung_jawab = $pjNama;
                $task->start_date       = '2026-01-01';
                $task->date_akhir       = '2026-12-31';
                $task->start_jam        = '08:00';
                $task->end_jam          = null;
                $task->duration         = 365;
                $task->status           = 'Sedang Berjalan';
                $task->progress         = 0;
                $task->owners           = implode(',', $memberNips);
                $task->save();
                $importedCount++;
            } else {
                $task->tim    = $nmTim;
                $task->owners = implode(',', $memberNips);
                $task->save();
                $updatedCount++;
            }

            // Simpan penugasan untuk setiap anggota proyek
            foreach ($members as $m) {
                $exists = penugasan::where('id_kegiatan', $task->id)
                    ->where('niplama', $m['nip'])
                    ->first();

                if (!$exists) {
                    $penugasan = new penugasan();
                    $penugasan->id_kegiatan      = $task->id;
                    $penugasan->niplama          = $m['nip'];
                    $penugasan->peserta          = $m['nama'];
                    $penugasan->status_kehadiran = 'Belum Hadir';
                    $penugasan->save();
                } else {
                    if (empty($exists->peserta) || $exists->peserta === '-') {
                        $exists->peserta = $m['nama'];
                        $exists->save();
                    }
                }
            }
        }

        $this->info("SELESAI!");
        $this->info("Kegiatan/Proyek baru diimpor: $importedCount");
        $this->info("Kegiatan/Proyek diperbarui: $updatedCount");
        return 0;
    }
}
