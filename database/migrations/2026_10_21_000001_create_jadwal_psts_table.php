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
        if (!Schema::hasTable('jadwal_psts')) {
            Schema::create('jadwal_psts', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal')->index();
                $table->integer('sesi')->default(1); // 1: Pagi (08:00 - 12:00), 2: Siang (13:00 - 16:00)
                $table->string('niplama', 30)->index();
                $table->string('nama_petugas', 255);
                $table->string('status_kehadiran', 50)->default('Terjadwal'); // 'Terjadwal', 'Hadir', 'Berhalangan', 'Digantikan'
                $table->text('alasan_berhalangan')->nullable();
                $table->string('status_tukar', 50)->default('none'); // 'none', 'minta_pengganti', 'bersedia', 'selesai'
                $table->string('pengganti_niplama', 30)->nullable();
                $table->string('pengganti_nama', 255)->nullable();
                $table->unsignedInteger('task_id')->nullable();
                $table->integer('penugasan_id')->nullable();
                $table->timestamp('waktu_kehadiran')->nullable();
                $table->timestamps();
            });
        }

        // Pastikan role Admin PST ada di master_groups
        if (Schema::hasTable('master_groups')) {
            $exists = \DB::table('master_groups')->where('grup', 'Admin PST')->exists();
            if (!$exists) {
                \DB::table('master_groups')->insert([
                    'grup' => 'Admin PST'
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_psts');
    }
};
