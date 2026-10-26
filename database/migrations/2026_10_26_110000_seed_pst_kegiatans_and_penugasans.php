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
            require_once database_path('seeds/PstKegiatanSeeder.php');
            $seeder = new \PstKegiatanSeeder();
            $seeder->run();
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
