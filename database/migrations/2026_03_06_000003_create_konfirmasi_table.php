<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konfirmasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tamu_id')->unique()->constrained('tamu')->cascadeOnDelete();
            $table->unsignedInteger('jumlah_hadir')->default(1);
            $table->boolean('butuh_antar_jemput')->nullable();
            $table->date('tanggal_datang')->nullable();
            $table->date('tanggal_pulang')->nullable();
            $table->boolean('butuh_penginapan')->nullable();
            $table->boolean('acknowledged')->default(false);
            $table->datetime('confirmed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konfirmasi');
    }
};
