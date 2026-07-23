<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penginapan', function (Blueprint $table) {
            $table->string('kategori', 20)->default('semua')->after('nama');
            $table->string('no_telp', 50)->nullable()->after('alamat');
            $table->string('koordinat_maps', 255)->nullable()->after('no_telp');
        });
    }

    public function down(): void
    {
        Schema::table('penginapan', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'no_telp', 'koordinat_maps']);
        });
    }
};
