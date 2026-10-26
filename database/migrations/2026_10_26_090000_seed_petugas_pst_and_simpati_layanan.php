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
        if (Schema::hasTable('petugas_layanans')) {
            require_once database_path('seeds/SimpatiPetugasLayananSeeder.php');
            $seeder = new \SimpatiPetugasLayananSeeder();
            $seeder->run();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe rollback: biarkan data petugas layanan tetap ada
    }
};
