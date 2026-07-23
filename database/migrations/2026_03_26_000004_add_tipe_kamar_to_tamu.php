<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tamu', function (Blueprint $table) {
            // null = auto (based on peserta data), double = forced double, twin = prefer twin
            $table->string('tipe_kamar', 10)->nullable()->after('dapat_transport');
        });
    }

    public function down(): void
    {
        Schema::table('tamu', function (Blueprint $table) {
            $table->dropColumn('tipe_kamar');
        });
    }
};
