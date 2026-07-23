<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konfirmasi', function (Blueprint $table) {
            $table->foreignId('sopir_id')->nullable()->after('butuh_penginapan')->constrained('sopir')->nullOnDelete();
            $table->foreignId('penginapan_id')->nullable()->after('sopir_id')->constrained('penginapan')->nullOnDelete();
            $table->string('nomor_kamar')->nullable()->after('penginapan_id');
        });
    }

    public function down(): void
    {
        Schema::table('konfirmasi', function (Blueprint $table) {
            $table->dropForeign(['sopir_id']);
            $table->dropForeign(['penginapan_id']);
            $table->dropColumn(['sopir_id', 'penginapan_id', 'nomor_kamar']);
        });
    }
};
