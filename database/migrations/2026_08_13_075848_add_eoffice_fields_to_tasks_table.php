<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('jenis_kegiatan')->default('Non-Rapat');
            $table->string('status_ruangan')->nullable();
            $table->string('status_pemimpin')->nullable();
            $table->unsignedBigInteger('surat_id')->nullable();
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->string('tim_dokumentasi')->nullable();
            $table->string('penanggung_jawab')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['parent_id', 'jenis_kegiatan', 'status_ruangan', 'status_pemimpin', 'surat_id', 'venue_id', 'tim_dokumentasi', 'penanggung_jawab']);
        });
    }
};
