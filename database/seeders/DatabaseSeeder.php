<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Settings
        Setting::updateOrCreate(
            ['nama' => 'tgl_terakhir_konfirmasi'],
            ['value' => config('undangan.confirmation_until')]
        );

        Setting::updateOrCreate(
            ['nama' => 'nama_acara'],
            ['value' => config('undangan.event_full_name')]
        );

        Setting::updateOrCreate(
            ['nama' => 'tagline'],
            ['value' => config('undangan.tagline')]
        );

        Setting::updateOrCreate(
            ['nama' => 'tanggal_acara'],
            ['value' => config('undangan.event_date')]
        );

        Setting::updateOrCreate(
            ['nama' => 'publish_lokasi_menginap'],
            ['value' => '0']
        );
    }
}
