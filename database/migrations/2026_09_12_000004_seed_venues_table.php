<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Seed 3 ruangan resmi + pastikan ada.
     * Id dibuat fix (1,2,3) karena kode mengacu ke id tersebut.
     */
    public function up(): void
    {
        if (!Schema::hasTable('venues')) {
            return;
        }

        $rows = [
            [
                'id' => 1,
                'name' => 'Aula Lantai 1',
                'description' => 'Lantai 1 Gedung BPS - Sound System, Proyektor, AC, Podium',
                'capacity' => 100,
            ],
            [
                'id' => 2,
                'name' => 'Vicon Lantai 3',
                'description' => 'Lantai 3 Gedung BPS - Smart TV Display, Camera Vicon 360, Mic Conference, AC',
                'capacity' => 25,
            ],
            [
                'id' => 3,
                'name' => 'Aula Lantai 4',
                'description' => 'Lantai 4 Gedung BPS - Videotron LED Screen, Sound System Besar, AC Central, Panggung',
                'capacity' => 150,
            ],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('venues')->where('id', $row['id'])->exists();
            if (!$exists) {
                DB::table('venues')->insert(array_merge($row, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        // data master, jangan dihapus
    }
};
