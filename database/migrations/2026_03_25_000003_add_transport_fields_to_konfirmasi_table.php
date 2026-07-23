<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konfirmasi', function (Blueprint $table) {
            // Flight info (optional, can be filled by transport)
            $table->string('pesawat_datang')->nullable()->after('tanggal_datang');
            $table->string('jam_tiba', 10)->nullable()->after('pesawat_datang');
            $table->string('pesawat_pulang')->nullable()->after('tanggal_pulang');
            $table->string('jam_berangkat', 10)->nullable()->after('pesawat_pulang');

            // Penjemputan tracking
            $table->string('status_penjemputan')->nullable()->after('sopir_id'); // null | dijemput | selesai
            $table->timestamp('dijemput_at')->nullable()->after('status_penjemputan');
            $table->string('dijemput_koordinat')->nullable()->after('dijemput_at');
            $table->timestamp('selesai_at')->nullable()->after('dijemput_koordinat');
            $table->string('selesai_koordinat')->nullable()->after('selesai_at');
        });
    }

    public function down(): void
    {
        Schema::table('konfirmasi', function (Blueprint $table) {
            $table->dropColumn([
                'pesawat_datang', 'jam_tiba',
                'pesawat_pulang', 'jam_berangkat',
                'status_penjemputan',
                'dijemput_at', 'dijemput_koordinat',
                'selesai_at', 'selesai_koordinat',
            ]);
        });
    }
};
