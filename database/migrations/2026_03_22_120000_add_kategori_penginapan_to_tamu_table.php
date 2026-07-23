<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tamu', function (Blueprint $table) {
            $table->string('kategori_penginapan', 50)->nullable()->after('penginapan');
        });
    }

    public function down(): void
    {
        Schema::table('tamu', function (Blueprint $table) {
            $table->dropColumn('kategori_penginapan');
        });
    }
};
