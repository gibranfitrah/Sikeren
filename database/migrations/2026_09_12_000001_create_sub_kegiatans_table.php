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
        if (!Schema::hasTable('sub_kegiatans')) {
            Schema::create('sub_kegiatans', function (Blueprint $table) {
                $table->id();
                // tasks.id = int(10) unsigned, jadi pakai unsignedInteger agar FK cocok
                $table->unsignedInteger('task_id')->nullable()->index();
                $table->string('nama_sub');
                $table->string('tim')->nullable();
                $table->string('pj')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->text('anggota')->nullable();
                $table->string('status')->default('Sedang Berjalan');
                $table->integer('progress')->default(0);
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_kegiatans');
    }
};
