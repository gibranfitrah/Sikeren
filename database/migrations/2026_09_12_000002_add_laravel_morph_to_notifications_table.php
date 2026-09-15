<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom morph Laravel agar DB::table('notifications')->insert
     * dengan type/notifiable_type/notifiable_id/data/read_at bisa jalan,
     * tanpa merusak kolom custom lama (niplama/judul/pesan/link/tipe/user_id/url).
     */
    public function up(): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'type')) {
                $table->string('type')->nullable()->after('id');
            }
            if (!Schema::hasColumn('notifications', 'notifiable_type')) {
                $table->string('notifiable_type')->nullable()->after('type');
            }
            if (!Schema::hasColumn('notifications', 'notifiable_id')) {
                $table->unsignedBigInteger('notifiable_id')->nullable()->after('notifiable_type');
            }
            if (!Schema::hasColumn('notifications', 'data')) {
                $table->text('data')->nullable()->after('notifiable_id');
            }
            if (!Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('data');
            }
        });

        // Catatan: kolom `id` tetap bigint auto-increment bawaan.
        // Insert morph tidak boleh memaksa uuid string (lihat SubKegiatanController),
        // biarkan DB yang generate id integer otomatis.
    }

    public function down(): void
    {
        // tidak drop kolom agar tidak merusak data
    }
};
