<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Resources\TamuResource\Pages\ListTamu;

class TamuTableStatsWidget extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected function getTablePage(): string
    {
        return ListTamu::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();

        $totalUndangan   = (clone $query)->count();
        $totalOrang      = (clone $query)->sum('jumlah_orang');
        $totalKonfirmasi = (clone $query)->whereHas('konfirmasi')->count();
        $percentage      = $totalUndangan > 0
            ? round(($totalKonfirmasi / $totalUndangan) * 100)
            : 0;
        $belum           = $totalUndangan - $totalKonfirmasi;

        $konfirmasiColor = match (true) {
            $percentage >= 75 => 'success',
            $percentage >= 40 => 'warning',
            default           => 'danger',
        };

        return [
            Stat::make('Total Undangan', number_format($totalUndangan))
                ->description('Jumlah data undangan dalam filter saat ini')
                ->icon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('Total Orang', number_format($totalOrang))
                ->description('Jumlah orang yang diundang (sum jumlah_orang)')
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Konfirmasi', $totalKonfirmasi . ' (' . $percentage . '%)')
                ->description($percentage . '% sudah konfirmasi · ' . $belum . ' belum')
                ->icon('heroicon-o-clipboard-document-check')
                ->color($konfirmasiColor),
        ];
    }
}
