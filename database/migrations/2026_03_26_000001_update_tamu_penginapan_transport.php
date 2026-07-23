<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tamu', function (Blueprint $table) {
            $table->dropColumn(['penginapan', 'kategori_penginapan']);

            $table->foreignId('penginapan_id')
                ->nullable()
                ->constrained('penginapan')
                ->nullOnDelete()
                ->after('luar_kota');

            $table->boolean('dapat_transport')
                ->default(false)
                ->after('penginapan_id');
        });
    }

    public function down(): void
    {
        Schema::table('tamu', function (Blueprint $table) {
            $table->dropConstrainedForeignId('penginapan_id');
            $table->dropColumn('dapat_transport');
            $table->boolean('penginapan')->default(false)->after('luar_kota');
            $table->string('kategori_penginapan', 50)->nullable()->after('penginapan');
        });
    }
};
