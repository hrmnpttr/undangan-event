<?php

namespace App\Filament\Pages;

use App\Exports\PenginapanExport;
use App\Services\RoomCalculatorService;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class SummaryPenginapan extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Summary Penginapan';

    protected static ?string $title = 'Summary Penginapan';

    protected static ?string $slug = 'summary-penginapan';

    protected static ?int $navigationSort = 5;

    protected static \UnitEnum|string|null $navigationGroup = 'Laporan';

    protected string $view = 'filament.pages.summary-penginapan';

    public static function canAccess(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return $user instanceof \App\Models\User
            && in_array($user->role, ['panitia', 'penginapan', 'panitia_penginapan'], true);
    }

    // 'total' | 'perhari'
    public string $activeTab = 'total';

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    private function myPenginapanId(): ?int
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return ($user instanceof \App\Models\User && $user->role === 'penginapan')
            ? $user->penginapan_id
            : null;
    }

    public function getTotalSummaryProperty(): Collection
    {
        $all = app(RoomCalculatorService::class)->buildPenginapanSummary();
        $pid = $this->myPenginapanId();
        return $pid ? $all->filter(fn ($row) => $row['id'] === $pid)->values() : $all;
    }

    public function getPerHariSummaryProperty(): Collection
    {
        $all = app(RoomCalculatorService::class)->buildPerHariSummary();
        $pid = $this->myPenginapanId();
        return $pid ? $all->filter(fn ($row) => ($row['penginapan_id'] ?? null) === $pid)->values() : $all;
    }

    public function getEstimasiDetailProperty(): Collection
    {
        $service = app(RoomCalculatorService::class);
        $pid     = $this->myPenginapanId();
        return $service->buildEstimasiDetail($pid);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_excel')
                ->label('Download Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $penginapanId = $this->myPenginapanId();
                    return response()->streamDownload(function () use ($penginapanId) {
                        echo Excel::raw(new PenginapanExport($penginapanId), \Maatwebsite\Excel\Excel::XLSX);
                    }, 'summary-penginapan-' . now()->format('Ymd-His') . '.xlsx');
                }),
        ];
    }
}
