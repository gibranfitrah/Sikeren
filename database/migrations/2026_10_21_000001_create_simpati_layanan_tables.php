<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petugas_layanans', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->index();
            $table->string('niplama', 15)->nullable()->index();
            $table->string('nipbaru', 20)->nullable();
            $table->string('nama', 200);
            $table->string('jabatan', 200)->nullable();
            $table->string('grup', 10)->index();
            $table->boolean('is_aktif')->default(true);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['user_id', 'grup']);
        });

        Schema::create('jadwal_layanans', function (Blueprint $table) {
            $table->id();
            $table->string('grup', 10)->index();
            $table->date('tanggal')->index();
            $table->tinyInteger('sesi')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->bigInteger('user_id')->nullable()->index();
            $table->string('niplama', 15)->nullable()->index();
            $table->string('nama', 200);
            $table->string('status', 30)->default('Terjadwal');
            $table->string('sumber', 20)->default('manual');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['grup', 'tanggal']);
        });

        Schema::create('simpati_admin_roles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->index();
            $table->string('role', 30);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['user_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simpati_admin_roles');
        Schema::dropIfExists('jadwal_layanans');
        Schema::dropIfExists('petugas_layanans');
    }
};
