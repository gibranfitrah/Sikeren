<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Pastikan seluruh kolom yang dibutuhkan di tabel tasks tersedia
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (!Schema::hasColumn('tasks', 'nomor')) {
                    $table->string('nomor', 100)->nullable();
                }
                if (!Schema::hasColumn('tasks', 'agenda')) {
                    $table->text('agenda')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'tempat')) {
                    $table->string('tempat', 255)->nullable();
                }
                if (!Schema::hasColumn('tasks', 'pemimpin')) {
                    $table->string('pemimpin', 255)->nullable();
                }
                if (!Schema::hasColumn('tasks', 'notulis')) {
                    $table->string('notulis', 255)->nullable();
                }
                if (!Schema::hasColumn('tasks', 'date_akhir')) {
                    $table->date('date_akhir')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'start_jam')) {
                    $table->time('start_jam')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'end_jam')) {
                    $table->time('end_jam')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'notulen')) {
                    $table->text('notulen')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'jenis')) {
                    $table->string('jenis', 50)->default('Kegiatan');
                }
                if (!Schema::hasColumn('tasks', 'owners')) {
                    $table->text('owners')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'status')) {
                    $table->string('status', 50)->default('Belum Mulai');
                }
                if (!Schema::hasColumn('tasks', 'alasan_status')) {
                    $table->text('alasan_status')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'surat')) {
                    $table->string('surat', 255)->nullable();
                }
                if (!Schema::hasColumn('tasks', 'setuju_rapat')) {
                    $table->boolean('setuju_rapat')->default(0);
                }
                if (!Schema::hasColumn('tasks', 'materi_link')) {
                    $table->text('materi_link')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'foto_link')) {
                    $table->text('foto_link')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'jenis_tujuan')) {
                    $table->string('jenis_tujuan', 100)->nullable();
                }
                if (!Schema::hasColumn('tasks', 'tujuan')) {
                    $table->string('tujuan', 255)->nullable();
                }
                if (!Schema::hasColumn('tasks', 'notulen_selesai')) {
                    $table->boolean('notulen_selesai')->default(0);
                }
                if (!Schema::hasColumn('tasks', 'parent_id')) {
                    $table->unsignedBigInteger('parent_id')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'jenis_kegiatan')) {
                    $table->string('jenis_kegiatan', 100)->nullable();
                }
                if (!Schema::hasColumn('tasks', 'status_ruangan')) {
                    $table->string('status_ruangan', 50)->nullable();
                }
                if (!Schema::hasColumn('tasks', 'status_pemimpin')) {
                    $table->string('status_pemimpin', 50)->nullable();
                }
                if (!Schema::hasColumn('tasks', 'surat_id')) {
                    $table->unsignedBigInteger('surat_id')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'venue_id')) {
                    $table->unsignedBigInteger('venue_id')->nullable();
                }
                if (!Schema::hasColumn('tasks', 'tim_dokumentasi')) {
                    $table->string('tim_dokumentasi', 255)->nullable();
                }
                if (!Schema::hasColumn('tasks', 'penanggung_jawab')) {
                    $table->string('penanggung_jawab', 255)->nullable();
                }
                if (!Schema::hasColumn('tasks', 'tim')) {
                    $table->string('tim', 255)->nullable();
                }
                if (!Schema::hasColumn('tasks', 'wilayah')) {
                    $table->text('wilayah')->nullable();
                }
            });
        }

        // 2. Jalankan seeder sinkronisasi kegiatan dan penugasan
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
