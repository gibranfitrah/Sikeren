<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tasks') && Schema::hasTable('penugasans')) {
            if (file_exists(database_path('seeders/KegiatanPenugasanLengkapSeeder.php'))) {
                require_once database_path('seeders/KegiatanPenugasanLengkapSeeder.php');
                $seeder = new \Database\Seeders\KegiatanPenugasanLengkapSeeder();
                $seeder->run();
            } elseif (file_exists(database_path('seeds/KegiatanPenugasanLengkapSeeder.php'))) {
                require_once database_path('seeds/KegiatanPenugasanLengkapSeeder.php');
                $seeder = new \KegiatanPenugasanLengkapSeeder();
                $seeder->run();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe rollback
    }
};
