<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tamu', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->unsignedInteger('jumlah_orang')->default(1);
            $table->enum('bahasa', ['ID', 'EN'])->default('ID');
            $table->boolean('luar_kota')->default(false);
            $table->boolean('penginapan')->default(false);
            $table->boolean('tipe')->default(false)->comment('0=offline/cetak, 1=online');
            $table->enum('jenis', ['umum', 'VIP', 'VVIP'])->default('umum');
            $table->string('kode_unik', 10)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tamu');
    }
};
