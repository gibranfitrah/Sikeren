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
        // 1. Tabel Master Proyek / Kegiatan
        if (!Schema::hasTable('proyeks')) {
            Schema::create('proyeks', function (Blueprint $table) {
                $table->id();
                $table->string('id_tim', 20)->nullable()->index();
                $table->string('nm_tim', 255)->nullable();
                $table->string('proyekid', 50)->unique();
                $table->string('namaproyek', 255);
                $table->string('pj_nama', 255)->nullable();
                $table->string('pj_nip', 50)->nullable()->index();
                $table->string('status', 50)->default('Aktif');
                $table->timestamps();
            });
        }

        // 2. Tabel Anggota / Penugasan Tiap Proyek
        if (!Schema::hasTable('anggota_proyeks')) {
            Schema::create('anggota_proyeks', function (Blueprint $table) {
                $table->id();
                $table->string('proyekid', 50)->index();
                $table->string('namaproyek', 255)->nullable();
                $table->string('id_tim', 20)->nullable()->index();
                $table->string('nm_tim', 255)->nullable();
                $table->string('niplama', 50)->index();
                $table->string('nama_lengkap', 255);
                $table->string('peran', 100)->default('Anggota');
                $table->timestamps();

                // Cegah duplikasi penugasan pegawai yang sama pada proyek yang sama
                $table->unique(['proyekid', 'niplama'], 'proyek_anggota_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota_proyeks');
        Schema::dropIfExists('proyeks');
    }
};
