<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('venues')) {
            return;
        }

        DB::table('venues')->where('id', 1)->update([
            'name' => 'Ruang Rapat Lantai 1',
            'capacity' => 24,
            'description' => 'Lantai 1 Gedung BPS • Sound System, Proyektor, AC, Podium (Kapasitas s.d 24 Orang)',
            'updated_at' => now(),
        ]);

        DB::table('venues')->where('id', 2)->update([
            'name' => 'Vicon Lantai 3',
            'capacity' => 58,
            'description' => 'Lantai 3 Gedung BPS • Smart TV Display, Camera Vicon 360, Mic Conference Polycom, AC (Kapasitas s.d 58 Orang)',
            'updated_at' => now(),
        ]);

        DB::table('venues')->where('id', 3)->update([
            'name' => 'Aula Lantai 4',
            'capacity' => 100,
            'description' => 'Lantai 4 Gedung BPS • Videotron LED Screen, Sound System Besar, AC Central, Panggung (Kapasitas s.d 100 Orang)',
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed for data update
    }
};
