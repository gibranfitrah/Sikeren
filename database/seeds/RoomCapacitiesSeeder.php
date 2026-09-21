<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomCapacitiesSeeder extends Seeder
{
    /**
     * Matriks kapasitas final (spreadsheet), venue existing id 1-3.
     * Idempotent: updateOrCreate berdasarkan venue_id + layout.
     * Tidak menyentuh tabel venues maupun room_bookings.
     */
    public function run()
    {
        $rows = [
            // Aula Lantai 1 (venue 1)
            ['venue_id' => 1, 'layout' => 'Theatre',   'capacity_without_sofa' => 24,  'capacity_with_sofa' => 20,   'with_sofa_available' => true],
            ['venue_id' => 1, 'layout' => 'Classroom', 'capacity_without_sofa' => 18,  'capacity_with_sofa' => 14,   'with_sofa_available' => true],
            ['venue_id' => 1, 'layout' => 'U-Shape',   'capacity_without_sofa' => 8,   'capacity_with_sofa' => null, 'with_sofa_available' => false],
            ['venue_id' => 1, 'layout' => 'Boardroom', 'capacity_without_sofa' => 12,  'capacity_with_sofa' => null, 'with_sofa_available' => false],
            ['venue_id' => 1, 'layout' => 'Round Table', 'capacity_without_sofa' => 12, 'capacity_with_sofa' => null, 'with_sofa_available' => false],
            ['venue_id' => 1, 'layout' => 'Hollow Square', 'capacity_without_sofa' => 10, 'capacity_with_sofa' => null, 'with_sofa_available' => false],
            ['venue_id' => 1, 'layout' => 'Custom Layout', 'capacity_without_sofa' => 24, 'capacity_with_sofa' => 20, 'with_sofa_available' => true],
            // Vicon Lantai 3 (venue 2)
            ['venue_id' => 2, 'layout' => 'Theatre',   'capacity_without_sofa' => 58,  'capacity_with_sofa' => 48,   'with_sofa_available' => true],
            ['venue_id' => 2, 'layout' => 'Classroom', 'capacity_without_sofa' => 48,  'capacity_with_sofa' => 40,   'with_sofa_available' => true],
            ['venue_id' => 2, 'layout' => 'U-Shape',   'capacity_without_sofa' => 23,  'capacity_with_sofa' => null, 'with_sofa_available' => false],
            ['venue_id' => 2, 'layout' => 'Boardroom', 'capacity_without_sofa' => 26,  'capacity_with_sofa' => null, 'with_sofa_available' => false],
            ['venue_id' => 2, 'layout' => 'Round Table', 'capacity_without_sofa' => 24, 'capacity_with_sofa' => null, 'with_sofa_available' => false],
            ['venue_id' => 2, 'layout' => 'Hollow Square', 'capacity_without_sofa' => 20, 'capacity_with_sofa' => null, 'with_sofa_available' => false],
            ['venue_id' => 2, 'layout' => 'Custom Layout', 'capacity_without_sofa' => 58, 'capacity_with_sofa' => 48, 'with_sofa_available' => true],
            // Aula Lantai 4 (venue 3)
            ['venue_id' => 3, 'layout' => 'Theatre',   'capacity_without_sofa' => 100, 'capacity_with_sofa' => 80,   'with_sofa_available' => true],
            ['venue_id' => 3, 'layout' => 'Classroom', 'capacity_without_sofa' => 62,  'capacity_with_sofa' => 52,   'with_sofa_available' => true],
            ['venue_id' => 3, 'layout' => 'U-Shape',   'capacity_without_sofa' => 58,  'capacity_with_sofa' => null, 'with_sofa_available' => false],
            ['venue_id' => 3, 'layout' => 'Boardroom', 'capacity_without_sofa' => 66,  'capacity_with_sofa' => null, 'with_sofa_available' => false],
            ['venue_id' => 3, 'layout' => 'Round Table', 'capacity_without_sofa' => 60, 'capacity_with_sofa' => null, 'with_sofa_available' => false],
            ['venue_id' => 3, 'layout' => 'Hollow Square', 'capacity_without_sofa' => 50, 'capacity_with_sofa' => null, 'with_sofa_available' => false],
            ['venue_id' => 3, 'layout' => 'Custom Layout', 'capacity_without_sofa' => 100, 'capacity_with_sofa' => 80, 'with_sofa_available' => true],
        ];

        foreach ($rows as $row) {
            // Lewati baris yang venue-nya tidak ada (jangan membuat venue baru)
            if (!DB::table('venues')->where('id', $row['venue_id'])->exists()) {
                continue;
            }

            DB::table('room_capacities')->updateOrInsert(
                ['venue_id' => $row['venue_id'], 'layout' => $row['layout']],
                array_merge($row, [
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ])
            );
        }

        // Idempotent via updateOrInsert venue_id + layout (21 baris untuk 3 venue x 7 layout kanonis)
        // (tidak menghapus baris venue lain di luar 1-3)
    }
}
