<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgendaKetuaTimsTable extends Migration
{
    public function up()
    {
        Schema::create('agenda_ketua_tim', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('perihal')->nullable();
            $table->string('agenda');
            $table->string('sub_kegiatan')->nullable();
            $table->text('dasar')->nullable();

            $table->date('tanggal')->nullable();
            $table->time('jam')->nullable();
            $table->string('tempat')->nullable();

            $table->string('pj')->nullable();
            $table->string('tujuan')->nullable();

            // Menyimpan daftar anggota tim lain
            $table->text('anggota')->nullable();

            $table->string('status')->default('terjadwal');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agenda_ketua_tim');
    }
}