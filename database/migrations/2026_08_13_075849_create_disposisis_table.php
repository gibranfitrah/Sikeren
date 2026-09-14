<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('disposisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_masuk_id')->constrained()->onDelete('cascade');
            $table->string('diteruskan_kepada');
            $table->text('instruksi')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposisis');
    }
};
