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
    if (!Schema::hasTable('notifications')) {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('niplama')->nullable();
            $table->string('judul')->nullable();
            $table->text('pesan')->nullable();
            $table->string('url')->nullable();
            $table->string('link')->nullable();
            $table->string('tipe')->nullable();
            $table->boolean('is_read')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        return;
    }

    // Tabel lama ada (struktur niplama/link) -> tambah kolom baru tanpa hapus data
    if (!Schema::hasColumn('notifications', 'user_id')) {
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });
    }
    if (!Schema::hasColumn('notifications', 'url')) {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('url')->nullable()->after('pesan');
        });
    }
    if (!Schema::hasColumn('notifications', 'niplama')) {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('niplama')->nullable()->after('user_id');
        });
    }
    if (!Schema::hasColumn('notifications', 'link')) {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('link')->nullable()->after('url');
        });
    }
    if (!Schema::hasColumn('notifications', 'tipe')) {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('tipe')->nullable()->after('link');
        });
    }

    // Backfill: user_id dari niplama jika numerik, url dari link
    try {
        \Illuminate\Support\Facades\DB::statement("UPDATE `notifications` SET `user_id` = CAST(`niplama` AS UNSIGNED) WHERE `user_id` IS NULL AND `niplama` REGEXP '^[0-9]+$'");
    } catch (\Exception $e) {}
    try {
        \Illuminate\Support\Facades\DB::statement("UPDATE `notifications` SET `url` = `link` WHERE `url` IS NULL AND `link` IS NOT NULL");
    } catch (\Exception $e) {}
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
