<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Matriks kapasitas ruangan (Alternatif B / lookup datar).
     * Satu baris = satu kombinasi venue + layout kanonis.
     * Aditif: tidak mengubah tabel venues maupun room_bookings.
     */
    public function up(): void
    {
        Schema::create('room_capacities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('venue_id');
            $table->string('layout', 50);
            $table->unsignedInteger('capacity_without_sofa');
            $table->unsignedInteger('capacity_with_sofa')->nullable();
            $table->boolean('with_sofa_available')->default(true);
            $table->timestamps();

            $table->unique(['venue_id', 'layout']);
            $table->foreign('venue_id')
                ->references('id')->on('venues')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_capacities');
    }
};
