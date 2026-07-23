<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('konfirmasi', function (Blueprint $table) {
            $table->json('catatan_perubahan')->nullable()->after('acknowledged');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konfirmasi', function (Blueprint $table) {
            $table->dropColumn('catatan_perubahan');
        });
    }
};
