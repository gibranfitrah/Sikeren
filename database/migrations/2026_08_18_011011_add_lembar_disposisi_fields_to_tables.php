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
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->string('nomor_agenda')->nullable();
            $table->string('lampiran')->nullable();
        });

        Schema::table('disposisis', function (Blueprint $table) {
            $table->string('tingkat_keamanan')->nullable();
            $table->date('tgl_penyelesaian')->nullable();
            $table->text('opsi_disposisi')->nullable(); // JSON array
            $table->text('diteruskan_kepada_tim')->nullable(); // JSON array
            $table->string('diteruskan_kepada_lainnya')->nullable();
            $table->text('diketahui')->nullable(); // JSON array
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->dropColumn(['tingkat_keamanan', 'tgl_penyelesaian', 'opsi_disposisi', 'diteruskan_kepada_tim', 'diteruskan_kepada_lainnya', 'diketahui']);
        });

        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->dropColumn(['nomor_agenda', 'lampiran']);
        });
    }
};
