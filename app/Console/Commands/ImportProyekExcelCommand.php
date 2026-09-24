<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\ProyekDatabaseSeeder;

class ImportProyekExcelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simpati:import-proyek {--path= : Path folder yang berisi file proyek.xlsx dan anggota_proyek.xlsx}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Impor data proyek dan anggota proyek dari file Excel ke database Sikeren';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== MEMULAI IMPOR DATABASE PROYEK & ANGGOTA DARI EXCEL ===");
        
        $seeder = new ProyekDatabaseSeeder();
        $seeder->setCommand($this);
        $seeder->run();

        $this->info("=== PROSES IMPOR SELESAI DENGAN SUKSES ===");
        return Command::SUCCESS;
    }
}
