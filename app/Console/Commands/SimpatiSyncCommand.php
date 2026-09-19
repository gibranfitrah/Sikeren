<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SimpatiApiService;

class SimpatiSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simpati:sync 
                            {--test : Hanya uji koneksi ke SIMPATI API tanpa sinkronisasi}
                            {--nip= : Ambil data spesifik pegawai berdasarkan NIP lama}
                            {--satker= : Filter berdasarkan ID Satker}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data Pegawai dan Tim Kerja dari SIMPATI API ke SIKEREN';

    /**
     * Execute the console command.
     */
    public function handle(SimpatiApiService $simpatiService)
    {
        $this->info('====================================================');
        $this->info('   INTEGRASI SIMPATI API -> SIKEREN (BPS SULTRA)   ');
        $this->info('====================================================');

        $baseUrl = config('services.simpati.base_url', 'http://localhost:3000');
        $apiKey  = config('services.simpati.api_key', '');
        $maskedKey = substr($apiKey, 0, 10) . '...' . substr($apiKey, -6);

        $this->line("• Base URL : <comment>{$baseUrl}</comment>");
        $this->line("• API Key  : <comment>{$maskedKey}</comment>");
        $this->newLine();

        // 1. Opsi --test
        if ($this->option('test')) {
            $this->info('Menguji koneksi ke SIMPATI API...');
            $result = $simpatiService->testConnection();
            if ($result['success']) {
                $this->info("✓ Sukses! {$result['message']}");
                $this->line("  Ditemukan {$result['data_count']} data pegawai aktif.");
                return 0;
            } else {
                $this->error("✗ Gagal: {$result['message']}");
                return 1;
            }
        }

        // 2. Opsi --nip
        $nip = $this->option('nip');
        if (!empty($nip)) {
            $this->info("Mengambil detail data pegawai untuk NIP: {$nip}...");
            try {
                $pegawai = $simpatiService->getPegawaiByNip($nip);
                if ($pegawai) {
                    $this->table(
                        ['Field', 'Value'],
                        [
                            ['Nama Lengkap', $pegawai['nama_lengkap'] ?? '-'],
                            ['NIP Lama', $pegawai['niplama'] ?? '-'],
                            ['NIP Baru', $pegawai['nipbaru'] ?? '-'],
                            ['Email', $pegawai['email'] ?? '-'],
                            ['Jabatan', $pegawai['nm_jabatan'] ?? '-'],
                            ['Satker', $pegawai['nm_satker'] ?? '-'],
                            ['Tim Kerja', !empty($pegawai['tims']) ? implode(', ', array_column($pegawai['tims'], 'nm_tim')) : '-'],
                        ]
                    );
                    return 0;
                } else {
                    $this->warn("Pegawai dengan NIP {$nip} tidak ditemukan.");
                    return 1;
                }
            } catch (\Exception $e) {
                $this->error("Error: " . $e->getMessage());
                return 1;
            }
        }

        // 3. Full Sync
        $this->info('Memulai proses sinkronisasi data dari SIMPATI API...');
        $this->output->progressStart(3);

        $this->output->progressAdvance();
        $this->line(' [1/3] Menghubungi SIMPATI API...');

        $result = $simpatiService->syncAll();
        $this->output->progressAdvance();
        $this->line(' [2/3] Memproses data pegawai & tim kerja...');

        $this->output->progressFinish();
        $this->newLine();

        if ($result['success']) {
            $this->info('✓ SINKRONISASI BERHASIL!');
            $this->table(
                ['Kategori Data', 'Jumlah'],
                [
                    ['Total Pegawai di SIMPATI', $result['pegawai_total']],
                    ['Pegawai Baru Dibuat di Sikeren', $result['pegawai_created']],
                    ['Pegawai Diperbarui di Sikeren', $result['pegawai_updated']],
                    ['Total Tim Kerja di SIMPATI', $result['tim_total']],
                    ['Tim Baru Ditambahkan', $result['tim_created']],
                    ['Keanggotaan Tim Disinkronkan', $result['anggota_synced']],
                ]
            );

            if (!empty($result['errors'])) {
                $this->warn('Beberapa peringatan saat sinkronisasi:');
                foreach ($result['errors'] as $err) {
                    $this->line(" - <fg=yellow>{$err}</>");
                }
            }

            return 0;
        } else {
            $this->error('✗ SINKRONISASI GAGAL!');
            foreach ($result['errors'] as $err) {
                $this->error(" - {$err}");
            }
            return 1;
        }
    }
}
