<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom presensi yang dipakai kode tapi belum ada di tabel penugasans:
     * - status_kehadiran (diisi/dibaca KegiatanController & LihatKegiatanController)
     * - keterangan (catatan presensi)
     * - waktu_kehadiran (timestamp saat presensi)
     * Tanpa kolom ini, halaman detail rapat gagal dengan SQLSTATE 42S22.
     */
    public function up(): void
    {
        Schema::table('penugasans', function (Blueprint $table) {
            if (!Schema::hasColumn('penugasans', 'status_kehadiran')) {
                $table->string('status_kehadiran', 50)->nullable();
            }
            if (!Schema::hasColumn('penugasans', 'keterangan')) {
                $table->string('keterangan')->nullable();
            }
            if (!Schema::hasColumn('penugasans', 'waktu_kehadiran')) {
                $table->timestamp('waktu_kehadiran')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penugasans', function (Blueprint $table) {
            $columns = [];
            foreach (['status_kehadiran', 'keterangan', 'waktu_kehadiran'] as $col) {
                if (Schema::hasColumn('penugasans', $col)) {
                    $columns[] = $col;
                }
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
