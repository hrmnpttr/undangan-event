<?php

namespace App\Filament\Resources\DetailTamuMenginapResource\Pages;

use App\Exports\PenginapanExport;
use App\Filament\Resources\DetailTamuMenginapResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListDetailTamuMenginap extends ListRecords
{
    protected static string $resource = DetailTamuMenginapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_excel')
                ->label('Download Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $user = \Illuminate\Support\Facades\Auth::user();
                    $penginapanId = ($user instanceof \App\Models\User && $user->role === 'penginapan')
                        ? $user->penginapan_id
                        : null;

                    return response()->streamDownload(function () use ($penginapanId) {
                        echo Excel::raw(new PenginapanExport($penginapanId), \Maatwebsite\Excel\Excel::XLSX);
                    }, 'detail-tamu-menginap-' . now()->format('Ymd-His') . '.xlsx');
                }),
        ];
    }
}
