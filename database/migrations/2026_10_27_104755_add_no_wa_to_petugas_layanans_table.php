<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petugas_layanans', function (Blueprint $table) {
            if (!Schema::hasColumn('petugas_layanans', 'no_wa')) {
                $table->string('no_wa', 30)->nullable()->after('catatan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('petugas_layanans', function (Blueprint $table) {
            if (Schema::hasColumn('petugas_layanans', 'no_wa')) {
                $table->dropColumn('no_wa');
            }
        });
    }
};
