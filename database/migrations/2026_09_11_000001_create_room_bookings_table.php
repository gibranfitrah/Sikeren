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
        if (!Schema::hasTable('room_bookings')) {
            Schema::create('room_bookings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id')->nullable()->index();
                $table->string('nama_acara');
                $table->string('penyelenggara')->nullable();
                $table->enum('tipe_pertemuan', ['offline', 'online', 'hybrid'])->default('offline');
                
                // Ruangan Fisik
                $table->unsignedBigInteger('venue_id')->nullable()->index();
                $table->string('nama_ruangan')->nullable();
                
                // Akun Zoom & Daring
                $table->string('zoom_account')->nullable(); // 'zoom_1', 'zoom_2', 'eksternal', 'none'
                $table->text('zoom_link')->nullable();
                $table->string('zoom_meeting_id')->nullable();
                $table->string('zoom_passcode')->nullable();
                $table->string('zoom_host_key')->nullable();
                
                // Waktu & Jadwal
                $table->date('booking_date');
                $table->time('start_time');
                $table->time('end_time');
                
                $table->string('status')->default('Disetujui'); // 'Menunggu', 'Disetujui', 'Dibatalkan'
                $table->text('keterangan')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_bookings');
    }
};
