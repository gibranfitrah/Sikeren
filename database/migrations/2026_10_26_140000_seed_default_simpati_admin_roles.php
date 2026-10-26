<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $seederFile = database_path('seeds/SimpatiAdminRoleSeeder.php');
        if (file_exists($seederFile)) {
            require_once $seederFile;
            $seeder = new \SimpatiAdminRoleSeeder();
            $seeder->run();
        }
    }

    public function down(): void
    {
        // Safe to leave untouched or truncate if needed
    }
};
