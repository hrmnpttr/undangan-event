<?php

namespace App\Filament\Widgets;

use App\Models\Konfirmasi;
use App\Models\Tamu;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatistikUndanganWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        $totalTamu = Tamu::count();
        $sudahKonfirmasi = Tamu::whereHas('konfirmasi')->count();
        $belumKonfirmasi = Tamu::whereDoesntHave('konfirmasi')->count();
        $sudahHadir = Tamu::whereHas('kehadirans')->distinct()->count();
        $tidakKonfirmasiTapiHadir = Tamu::whereDoesntHave('konfirmasi')
            ->whereHas('kehadirans')
            ->count();
        $totalOrangKonfirmasi = Konfirmasi::sum('jumlah_hadir');

        return [
            Stat::make('Total Undangan', $totalTamu)
                ->description('Jumlah data undangan')
                ->icon('heroicon-o-user-group')
                ->color('primary'),
            Stat::make('Sudah Konfirmasi', $sudahKonfirmasi)
                ->description($totalOrangKonfirmasi . ' orang akan hadir')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success'),
            Stat::make('Belum Konfirmasi', $belumKonfirmasi)
                ->description('Belum memberikan konfirmasi')
                ->icon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('Sudah Hadir', $sudahHadir)
                ->description('Tercatat di scan kehadiran')
                ->icon('heroicon-o-check-badge')
                ->color('info'),
            Stat::make('Hadir Tanpa Konfirmasi', $tidakKonfirmasiTapiHadir)
                ->description('Tidak konfirmasi tapi hadir')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
