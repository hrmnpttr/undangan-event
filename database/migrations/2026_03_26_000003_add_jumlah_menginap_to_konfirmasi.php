<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konfirmasi', function (Blueprint $table) {
            $table->unsignedTinyInteger('jumlah_menginap')
                ->nullable()
                ->after('butuh_penginapan');
        });
    }

    public function down(): void
    {
        Schema::table('konfirmasi', function (Blueprint $table) {
            $table->dropColumn('jumlah_menginap');
        });
    }
};
