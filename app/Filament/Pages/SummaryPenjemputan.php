<?php

namespace App\Filament\Pages;

use App\Exports\PenjemputanExport;
use App\Services\RoomCalculatorService;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class SummaryPenjemputan extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Summary Penjemputan';

    protected static ?string $title = 'Summary Penjemputan';

    protected static ?string $slug = 'summary-penjemputan';

    public static function canAccess(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return $user instanceof \App\Models\User && $user->isPanitia();
    }

    protected static ?int $navigationSort = 6;

    protected static \UnitEnum|string|null $navigationGroup = 'Laporan';

    protected string $view = 'filament.pages.summary-penjemputan';

    // 'total' | 'perhari'
    public string $activeTab = 'total';

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getTotalProperty(): array
    {
        return app(RoomCalculatorService::class)->buildPenjemputanTotal();
    }

    public function getPerHariProperty(): Collection
    {
        return app(RoomCalculatorService::class)->buildPenjemputanPerHari();
    }

    public function getMobilProperty(): Collection
    {
        return app(RoomCalculatorService::class)->buildPenjemputanPerHari();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_excel')
                ->label('Download Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    return response()->streamDownload(function () {
                        echo Excel::raw(new PenjemputanExport(), \Maatwebsite\Excel\Excel::XLSX);
                    }, 'summary-penjemputan-' . now()->format('Ymd-His') . '.xlsx');
                }),
        ];
    }
}
