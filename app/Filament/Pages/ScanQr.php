<?php

namespace App\Filament\Pages;

use App\Models\Kehadiran;
use App\Models\Tamu;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\On;

class ScanQr extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Scan Kehadiran';

    protected static ?string $title = 'Scan Kehadiran';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.scan-qr';

    public static function canAccess(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return $user instanceof \App\Models\User
            && in_array($user->role, ['panitia', 'scan'], true);
    }

    public string $mode = 'hardware'; // 'camera' or 'hardware'

    public bool $cameraActive = false;

    public string $manualInput = '';

    public ?array $lastScanned = null;

    public array $scanHistory = [];

    public function switchMode(string $mode): void
    {
        $this->mode = $mode;
        $this->manualInput = '';
        $this->cameraActive = ($mode === 'camera');
    }

    public function startScanning(): void
    {
        $this->cameraActive = true;
    }

    public function stopScanning(): void
    {
        $this->cameraActive = false;
    }

    public function processScan(string $kode): void
    {
        $kode = trim($kode);

        if (empty($kode)) {
            Notification::make()
                ->title('Kode kosong')
                ->danger()
                ->send();
            return;
        }

        // Extract kode_unik from URL if full URL is scanned
        if (str_contains($kode, '/i/')) {
            $parts = explode('/i/', $kode);
            $kode = end($parts);
        }

        $tamu = Tamu::where('kode_unik', $kode)->first();

        if (!$tamu) {
            Notification::make()
                ->title('Tamu tidak ditemukan')
                ->body('Kode: ' . $kode)
                ->danger()
                ->send();

            $this->lastScanned = [
                'found' => false,
                'kode' => $kode,
            ];

            return;
        }

        // Save attendance record
        Kehadiran::create([
            'tamu_id' => $tamu->id,
            'scanned_at' => now(),
        ]);

        $jumlahScan = $tamu->kehadirans()->count();
        $konfirmasi = $tamu->konfirmasi;
        $sudahKonfirmasi = $konfirmasi !== null;

        $data = [
            'found' => true,
            'nama' => $tamu->nama,
            'jenis' => $tamu->jenis,
            'jumlah_scan' => $jumlahScan,
            'jumlah_orang' => $tamu->jumlah_orang,
            'jumlah_konfirmasi' => $sudahKonfirmasi ? $konfirmasi->jumlah_hadir : null,
            'sudah_konfirmasi' => $sudahKonfirmasi,
            'waktu' => now()->format('H:i:s'),
            'kode' => $tamu->kode_unik,
        ];

        $this->lastScanned = $data;

        // Add to history (max 20)
        array_unshift($this->scanHistory, $data);
        $this->scanHistory = array_slice($this->scanHistory, 0, 20);

        // Clear input
        $this->manualInput = '';

        // Notification
        $title = $tamu->nama;
        if ($tamu->jenis !== 'umum') {
            $title .= ' — ' . $tamu->jenis;
        }

        $notification = Notification::make()->title($title);

        if (!$sudahKonfirmasi) {
            $notification->body('⚠️ Belum konfirmasi! Scan ke-' . $jumlahScan)
                ->warning();
        } elseif ($jumlahScan > 1) {
            $notification->body('Scan ke-' . $jumlahScan . ' | Konfirmasi: ' . $konfirmasi->jumlah_hadir . ' orang')
                ->info();
        } else {
            $notification->body('Scan ke-1 | Konfirmasi: ' . $konfirmasi->jumlah_hadir . ' orang')
                ->success();
        }

        $notification->send();

        // In camera mode, stop after one scan
        if ($this->mode === 'camera') {
            $this->cameraActive = false;
        }

        // Dispatch event to refocus input (hardware mode)
        $this->dispatch('scan-processed');
    }

    public function submitManual(): void
    {
        $this->processScan($this->manualInput);
    }
}
