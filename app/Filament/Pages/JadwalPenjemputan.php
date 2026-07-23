<?php

namespace App\Filament\Pages;

use App\Filament\Resources\KonfirmasiResource;
use App\Models\Konfirmasi;
use App\Models\Setting;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class JadwalPenjemputan extends Page
{
    public static function canAccess(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return $user instanceof \App\Models\User && $user->isPanitia();
    }

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Jadwal Penjemputan';

    protected static ?string $title = 'Jadwal Penjemputan';

    protected static ?string $slug = 'jadwal-penjemputan';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.jadwal-penjemputan';

    public string $tipeView = 'datang';

    public function switchTipe(string $tipe): void
    {
        $this->tipeView = $tipe;
    }

    /**
     * Build summary data grouped per tanggal.
     */
    public function getSummaryProperty(): Collection
    {
        $column = $this->tipeView === 'pulang' ? 'tanggal_pulang' : 'tanggal_datang';
        $tanggalAcara = Setting::getValue('tanggal_acara', config('undangan.event_date'));

        $konfirmasi = Konfirmasi::query()
            ->with('tamu')
            ->where('butuh_antar_jemput', true)
            ->whereNotNull($column)
            ->get();

        return $konfirmasi
            ->groupBy(fn ($k) => $k->{$column}?->format('Y-m-d'))
            ->map(function ($group, $tanggal) use ($tanggalAcara) {
                $umum = $group->filter(fn ($k) => ! in_array($k->tamu?->jenis, ['VIP', 'VVIP']));
                $vip = $group->filter(fn ($k) => $k->tamu?->jenis === 'VIP');
                $vvip = $group->filter(fn ($k) => $k->tamu?->jenis === 'VVIP');

                $adaPerubahan = $group->contains(function ($k) {
                    return ! $k->acknowledged || $k->jumlah_perubahan_baru > 0;
                });

                return [
                    'tanggal' => $tanggal,
                    'label' => Carbon::parse($tanggal)->translatedFormat('D, d M Y'),
                    'is_hari_h' => $tanggal === $tanggalAcara,
                    'umum' => $umum->count(),
                    'umum_orang' => (int) $umum->sum('jumlah_hadir'),
                    'vip' => $vip->count(),
                    'vip_orang' => (int) $vip->sum('jumlah_hadir'),
                    'vvip' => $vvip->count(),
                    'vvip_orang' => (int) $vvip->sum('jumlah_hadir'),
                    'total_tamu' => $group->count(),
                    'total_orang' => (int) $group->sum('jumlah_hadir'),
                    'ada_perubahan' => $adaPerubahan,
                    'detail_url' => $this->getDetailUrl($tanggal),
                ];
            })
            ->sortBy('tanggal')
            ->values();
    }

    /**
     * Build URL to Konfirmasi list filtered by date + antar jemput.
     */
    protected function getDetailUrl(string $tanggal): string
    {
        $filterKey = $this->tipeView === 'pulang' ? 'tanggal_pulang' : 'tanggal_datang';

        // Filament v5 maps table filter state to the URL via #[Url(as: 'filters')]
        $params = [
            'filters' => [
                $filterKey => [$filterKey => $tanggal],
                'butuh_antar_jemput' => ['value' => '1'],
            ],
        ];

        return KonfirmasiResource::getUrl('index', $params);
    }

    /**
     * Acknowledge all konfirmasi for a specific date.
     */
    public function acknowledgeDate(string $tanggal): void
    {
        $column = $this->tipeView === 'pulang' ? 'tanggal_pulang' : 'tanggal_datang';

        $konfirmasis = Konfirmasi::query()
            ->where('butuh_antar_jemput', true)
            ->whereDate($column, $tanggal)
            ->get();

        foreach ($konfirmasis as $konfirmasi) {
            $log = $konfirmasi->catatan_perubahan ?? [];
            $log = array_map(function ($entry) {
                $entry['acknowledged'] = true;

                return $entry;
            }, $log);

            $konfirmasi->update([
                'acknowledged' => true,
                'catatan_perubahan' => $log,
            ]);
        }
    }

    public function getSubheading(): string|Htmlable|null
    {
        $tanggalAcara = Setting::getValue('tanggal_acara', config('undangan.event_date'));
        $eventDate = Carbon::parse($tanggalAcara);

        $stats = $this->getSummaryStats();

        return new HtmlString(
            '<div class="flex flex-wrap gap-4 text-sm">'.
            '<span>📅 Hari H: <strong>'.$eventDate->translatedFormat('l, d F Y').'</strong></span>'.
            '<span>|</span>'.
            '<span>🛬 Datang: <strong>'.$stats['datang'].'</strong> tamu ('.$stats['datang_orang'].' org)</span>'.
            '<span>|</span>'.
            '<span>🛫 Pulang: <strong>'.$stats['pulang'].'</strong> tamu ('.$stats['pulang_orang'].' org)</span>'.
            ($stats['baru'] > 0 ? '<span>|</span><span class="text-warning-600 dark:text-warning-400">⚠ '.$stats['baru'].' belum diketahui</span>' : '').
            '</div>'
        );
    }

    protected function getSummaryStats(): array
    {
        $base = Konfirmasi::where('butuh_antar_jemput', true)->whereNotNull('tanggal_datang');

        return [
            'datang' => (clone $base)->count(),
            'datang_orang' => (clone $base)->sum('jumlah_hadir'),
            'pulang' => (clone $base)->whereNotNull('tanggal_pulang')->count(),
            'pulang_orang' => (clone $base)->whereNotNull('tanggal_pulang')->sum('jumlah_hadir'),
            'baru' => (clone $base)->where('acknowledged', false)->count(),
        ];
    }
}
