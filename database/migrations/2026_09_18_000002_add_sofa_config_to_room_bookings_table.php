<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mencatat pilihan konfigurasi sofa per booking baru.
     * Aditif + nullable: booking lama (NULL) tidak berubah dan tetap valid.
     * Nilai: 'tanpa_sofa' | 'dengan_sofa'.
     */
    public function up(): void
    {
        Schema::table('room_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('room_bookings', 'sofa_config')) {
                $table->string('sofa_config', 20)->nullable()->after('layout_meja');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('room_bookings', 'sofa_config')) {
                $table->dropColumn('sofa_config');
            }
        });
    }
};
