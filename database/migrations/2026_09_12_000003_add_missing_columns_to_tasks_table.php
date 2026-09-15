<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom yang dipakai kode tapi belum ada di tabel tasks:
     * - tim (filter TimeSchedule, fillable Task, diisi KetuaTimController)
     * - wilayah (JSON, fillable Task, diisi KetuaTimController)
     * - notulen_selesai (flag 0/1, dipakai Dashboard, TugasSaya, LihatKegiatan)
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'tim')) {
                $table->string('tim')->nullable()->after('text');
            }
            if (!Schema::hasColumn('tasks', 'wilayah')) {
                $table->text('wilayah')->nullable()->after('tim');
            }
            if (!Schema::hasColumn('tasks', 'notulen_selesai')) {
                $table->boolean('notulen_selesai')->default(0)->after('notulen');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $columns = [];
            foreach (['tim', 'wilayah', 'notulen_selesai'] as $col) {
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
