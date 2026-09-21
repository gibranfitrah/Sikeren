<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom yang dipakai kode tapi belum ada di tabel tasks:
     * - materi_link (diisi RapatController@store_rapat, LihatKegiatanController notulen)
     * - foto_link (diisi LihatKegiatanController notulen)
     * Tanpa kolom ini, simpan rapat/notulen gagal dengan SQLSTATE 42S22.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'materi_link')) {
                $table->string('materi_link', 500)->nullable();
            }
            if (!Schema::hasColumn('tasks', 'foto_link')) {
                $table->string('foto_link', 500)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $columns = [];
            foreach (['materi_link', 'foto_link'] as $col) {
                if (Schema::hasColumn('tasks', $col)) {
                    $columns[] = $col;
                }
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
