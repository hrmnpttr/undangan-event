<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tamu', function (Blueprint $table) {
            $table->char('jenis_kelamin', 1)->nullable()->after('tipe_kamar')
                  ->comment('L = Laki-laki, P = Perempuan');
        });
    }

    public function down(): void
    {
        Schema::table('tamu', function (Blueprint $table) {
            $table->dropColumn('jenis_kelamin');
        });
    }
};
