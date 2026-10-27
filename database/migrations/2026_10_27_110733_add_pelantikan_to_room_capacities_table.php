<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('room_capacities')) {
            $data = [
                [
                    'venue_id' => 1,
                    'layout' => 'Pelantikan',
                    'capacity_without_sofa' => 24,
                    'capacity_with_sofa' => 20,
                    'with_sofa_available' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'venue_id' => 2,
                    'layout' => 'Pelantikan',
                    'capacity_without_sofa' => 50,
                    'capacity_with_sofa' => 40,
                    'with_sofa_available' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'venue_id' => 3,
                    'layout' => 'Pelantikan',
                    'capacity_without_sofa' => 100,
                    'capacity_with_sofa' => 80,
                    'with_sofa_available' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($data as $item) {
                DB::table('room_capacities')->updateOrInsert(
                    ['venue_id' => $item['venue_id'], 'layout' => $item['layout']],
                    $item
                );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('room_capacities')) {
            DB::table('room_capacities')->where('layout', 'Pelantikan')->delete();
        }
    }
};
