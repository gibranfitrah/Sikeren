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
        Schema::table('room_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('room_bookings', 'setup_podium')) {
                $table->text('setup_podium')->nullable()->after('layout_meja');
            }
            if (!Schema::hasColumn('room_bookings', 'special_requests')) {
                $table->text('special_requests')->nullable()->after('setup_podium');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('room_bookings', 'special_requests')) {
                $table->dropColumn('special_requests');
            }
            if (Schema::hasColumn('room_bookings', 'setup_podium')) {
                $table->dropColumn('setup_podium');
            }
        });
    }
};
