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
        Schema::table('room_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('room_bookings', 'jumlah_peserta')) {
                $table->integer('jumlah_peserta')->nullable()->after('penyelenggara');
            }
            if (!Schema::hasColumn('room_bookings', 'fasilitas')) {
                $table->text('fasilitas')->nullable()->after('nama_ruangan');
            }
            if (!Schema::hasColumn('room_bookings', 'layout_meja')) {
                $table->string('layout_meja')->nullable()->after('fasilitas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('room_bookings', 'layout_meja')) {
                $table->dropColumn('layout_meja');
            }
            if (Schema::hasColumn('room_bookings', 'fasilitas')) {
                $table->dropColumn('fasilitas');
            }
            if (Schema::hasColumn('room_bookings', 'jumlah_peserta')) {
                $table->dropColumn('jumlah_peserta');
            }
        });
    }
};
