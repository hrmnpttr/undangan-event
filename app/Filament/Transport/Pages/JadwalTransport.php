<?php

namespace App\Filament\Transport\Pages;

use App\Models\Konfirmasi;
use App\Models\Sopir;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

class JadwalTransport extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Jadwal Hari Ini';
    protected static ?string $title = 'Jadwal Penjemputan';
    protected static ?string $slug = 'jadwal';
    protected string $view = 'filament.transport.pages.jadwal-transport';

    public string $tipeView = 'datang';
    public ?string $selectedDate = null;
    public array $flightData = [];

    public function mount(): void
    {
        // Only transport role can access this panel
        if (auth()->user()?->role !== 'transport') {
            abort(403, 'Akses ditolak. Panel ini hanya untuk petugas transport.');
        }
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function getMyJadwalProperty(): Collection
    {
        $sopir = Sopir::where('user_id', auth()->id())->first();
        if (! $sopir) {
            return collect();
        }

        $column  = $this->tipeView === 'pulang' ? 'tanggal_pulang' : 'tanggal_datang';
        $tanggal = $this->selectedDate ?? now()->format('Y-m-d');

        return Konfirmasi::query()
            ->with(['tamu', 'sopir'])
            ->where('sopir_id', $sopir->id)
            ->where('butuh_antar_jemput', true)
            ->whereDate($column, $tanggal)
            ->orderBy($this->tipeView === 'pulang' ? 'jam_berangkat' : 'jam_tiba')
            ->get();
    }

    public function getJadwalMendatangProperty(): Collection
    {
        $sopir = Sopir::where('user_id', auth()->id())->first();
        if (! $sopir) {
            return collect();
        }

        $today = now()->format('Y-m-d');

        // Collect future dates from both tanggal_datang and tanggal_pulang
        $datang = Konfirmasi::query()
            ->with('tamu')
            ->where('sopir_id', $sopir->id)
            ->where('butuh_antar_jemput', true)
            ->whereDate('tanggal_datang', '>', $today)
            ->orderBy('tanggal_datang')
            ->get()
            ->map(fn ($k) => [
                'tanggal'   => $k->tanggal_datang,
                'label'     => Carbon::parse($k->tanggal_datang)->translatedFormat('D, d M Y'),
                'tipe'      => 'datang',
                'tamu_nama' => $k->tamu?->nama,
                'tamu_jenis'=> $k->tamu?->jenis,
                'jumlah'    => $k->jumlah_hadir,
                'pesawat'   => $k->pesawat_datang,
                'jam'       => $k->jam_tiba,
            ]);

        $pulang = Konfirmasi::query()
            ->with('tamu')
            ->where('sopir_id', $sopir->id)
            ->where('butuh_antar_jemput', true)
            ->whereDate('tanggal_pulang', '>', $today)
            ->orderBy('tanggal_pulang')
            ->get()
            ->map(fn ($k) => [
                'tanggal'   => $k->tanggal_pulang,
                'label'     => Carbon::parse($k->tanggal_pulang)->translatedFormat('D, d M Y'),
                'tipe'      => 'pulang',
                'tamu_nama' => $k->tamu?->nama,
                'tamu_jenis'=> $k->tamu?->jenis,
                'jumlah'    => $k->jumlah_hadir,
                'pesawat'   => $k->pesawat_pulang,
                'jam'       => $k->jam_berangkat,
            ]);

        return $datang->concat($pulang)
            ->sortBy('tanggal')
            ->groupBy('label')
            ->map(fn ($items, $label) => [
                'label' => $label,
                'tanggal' => $items->first()['tanggal'],
                'items'   => $items->values(),
            ])
            ->values();
    }

    public function switchTipe(string $tipe): void
    {
        $this->tipeView = $tipe;
    }

    // ─── Check-in: mulai jemput ───────────────────────────────────────────────
    public function checkInAction(): Action
    {
        return Action::make('checkIn')
            ->label('Mulai Jemput')
            ->icon('heroicon-o-play')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Check-in')
            ->modalDescription('Tandai bahwa Anda sedang dalam perjalanan menjemput tamu ini?')
            ->extraAttributes(['x-data' => '{}'])
            ->action(function (array $arguments) {
                $this->doCheckIn($arguments['konfirmasi_id'] ?? null, $arguments['koordinat'] ?? null);
            });
    }

    public function doCheckIn(int $konfirmasiId, ?string $koordinat = null): void
    {
        $sopir = Sopir::where('user_id', auth()->id())->first();
        $konfirmasi = Konfirmasi::find($konfirmasiId);

        if (! $konfirmasi || ! $sopir || $konfirmasi->sopir_id !== $sopir->id) {
            Notification::make()->title('Tidak diizinkan')->danger()->send();
            return;
        }

        $konfirmasi->update([
            'status_penjemputan' => 'dijemput',
            'dijemput_at'        => now(),
            'dijemput_koordinat' => $koordinat,
        ]);

        Notification::make()->title('Check-in berhasil')->success()->send();
        $this->dispatch('penjemputan-updated');
    }

    // ─── Check-out: selesai jemput ────────────────────────────────────────────
    public function checkOutAction(): Action
    {
        return Action::make('checkOut')
            ->label('Selesai')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Selesai')
            ->modalDescription('Tandai penjemputan tamu ini sudah selesai?')
            ->action(function (array $arguments) {
                $this->doCheckOut($arguments['konfirmasi_id'] ?? null, $arguments['koordinat'] ?? null);
            });
    }

    public function doCheckOut(int $konfirmasiId, ?string $koordinat = null): void
    {
        $sopir = Sopir::where('user_id', auth()->id())->first();
        $konfirmasi = Konfirmasi::find($konfirmasiId);

        if (! $konfirmasi || ! $sopir || $konfirmasi->sopir_id !== $sopir->id) {
            Notification::make()->title('Tidak diizinkan')->danger()->send();
            return;
        }

        $konfirmasi->update([
            'status_penjemputan' => 'selesai',
            'selesai_at'         => now(),
            'selesai_koordinat'  => $koordinat,
        ]);

        Notification::make()->title('Penjemputan selesai')->success()->send();
        $this->dispatch('penjemputan-updated');
    }

    // ─── Save flight info directly from modal ────────────────────────────────
    public function saveFlightDirect(int $konfirmasiId): void
    {
        $sopir = Sopir::where('user_id', auth()->id())->first();
        $konfirmasi = Konfirmasi::find($konfirmasiId);

        if (! $konfirmasi || ! $sopir || $konfirmasi->sopir_id !== $sopir->id) {
            Notification::make()->title('Tidak diizinkan')->danger()->send();
            return;
        }

        $data = $this->flightData[$konfirmasiId] ?? [];
        $konfirmasi->update(array_filter($data, fn($v) => $v !== null && $v !== ''));
        $this->flightData[$konfirmasiId] = [];
        Notification::make()->title('Info penerbangan disimpan')->success()->send();
        $this->dispatch('close-modal', id: 'flight-modal-' . $konfirmasiId);
        $this->dispatch('penjemputan-updated');
    }

    // ─── Edit info penerbangan ────────────────────────────────────────────────
    public function editFlightAction(): Action
    {
        return Action::make('editFlight')
            ->label('Info Penerbangan')
            ->icon('heroicon-o-paper-airplane')
            ->color('gray')
            ->form(function (array $arguments): array {
                $k = Konfirmasi::find($arguments['konfirmasi_id'] ?? null);
                return $this->tipeView === 'pulang'
                    ? [
                        TextInput::make('pesawat_pulang')
                            ->label('Kode / Nama Pesawat')
                            ->default($k?->pesawat_pulang)
                            ->placeholder('e.g. GA-212'),
                        TextInput::make('jam_berangkat')
                            ->label('Jam Berangkat')
                            ->default($k?->jam_berangkat)
                            ->placeholder('e.g. 14:30'),
                    ]
                    : [
                        TextInput::make('pesawat_datang')
                            ->label('Kode / Nama Pesawat')
                            ->default($k?->pesawat_datang)
                            ->placeholder('e.g. GA-112'),
                        TextInput::make('jam_tiba')
                            ->label('Jam Tiba')
                            ->default($k?->jam_tiba)
                            ->placeholder('e.g. 09:15'),
                    ];
            })
            ->action(function (array $data, array $arguments) {
                $konfirmasi = Konfirmasi::find($arguments['konfirmasi_id'] ?? null);
                if (! $konfirmasi) return;

                $konfirmasi->update(array_filter($data, fn ($v) => $v !== null));
                Notification::make()->title('Info penerbangan disimpan')->success()->send();
                $this->dispatch('penjemputan-updated');
            });
    }
}
