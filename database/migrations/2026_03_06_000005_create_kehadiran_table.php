<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tamu_id')->constrained('tamu')->cascadeOnDelete();
            $table->datetime('scanned_at');
            $table->timestamps();

            $table->index('tamu_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};
