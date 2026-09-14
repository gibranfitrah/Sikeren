<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAnggotaToAgendaKetuaTimTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('agenda_ketua_tim', 'anggota')) {
            Schema::table('agenda_ketua_tim', function (Blueprint $table) {
                $table->text('anggota')->nullable()->after('tujuan');
            });
        }
    }

    public function down()
    {
        Schema::table('agenda_ketua_tim', function (Blueprint $table) {
            $table->dropColumn('anggota');
        });
    }
}