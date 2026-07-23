<?php

namespace App\Filament\Resources\KonfirmasiResource\Pages;

use App\Filament\Resources\KonfirmasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKonfirmasi extends ViewRecord
{
    protected static string $resource = KonfirmasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('acknowledge')
                ->label('Tandai Diketahui')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->getRecord();
                    $log = $record->catatan_perubahan ?? [];
                    $log = array_map(function ($entry) {
                        $entry['acknowledged'] = true;
                        return $entry;
                    }, $log);
                    $record->update([
                        'acknowledged' => true,
                        'catatan_perubahan' => $log,
                    ]);
                    $this->refreshFormData(['catatan_perubahan', 'acknowledged']);
                })
                ->visible(fn () => !$this->getRecord()->acknowledged),
        ];
    }
}
